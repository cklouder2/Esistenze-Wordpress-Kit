/**
 * admin-edit-advanced.js
 * Advanced content editing functionality for admin panel
 */

// Import required modules
import { useState, useEffect } from 'react';
import axios from 'axios';
import { toast } from 'react-toastify';

// Configuration
const API_ENDPOINT = '/api/admin/content';
const DEFAULT_EDITOR_CONFIG = {
  toolbar: [
    ['bold', 'italic', 'underline', 'strike'],
    ['blockquote', 'code-block'],
    [{ 'header': 1 }, { 'header': 2 }],
    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
    [{ 'script': 'sub'}, { 'script': 'super' }],
    [{ 'indent': '-1'}, { 'indent': '+1' }],
    [{ 'direction': 'rtl' }],
    [{ 'size': ['small', false, 'large', 'huge'] }],
    [{ 'color': [] }, { 'background': [] }],
    [{ 'font': [] }],
    [{ 'align': [] }],
    ['clean'],
    ['link', 'image', 'video']
  ]
};

/**
 * Advanced Content Editor Component
 * @param {Object} props Component properties
 * @param {string} props.contentId ID of the content to edit
 * @param {string} props.contentType Type of content (article, page, product, etc.)
 * @param {Function} props.onSave Callback after successful save
 * @param {Function} props.onCancel Callback when editing is cancelled
 */
export const AdvancedEditor = ({ contentId, contentType, onSave, onCancel }) => {
  // State management
  const [content, setContent] = useState(null);
  const [editor, setEditor] = useState(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);
  const [history, setHistory] = useState([]);
  const [historyIndex, setHistoryIndex] = useState(-1);
  const [versionInfo, setVersionInfo] = useState([]);
  const [selectedVersion, setSelectedVersion] = useState(null);
  const [permissions, setPermissions] = useState({
    canPublish: false,
    canDelete: false,
    canCreateVersion: false
  });
  const [meta, setMeta] = useState({
    title: '',
    slug: '',
    tags: [],
    featuredImage: '',
    description: '',
    publishDate: null,
    status: 'draft'
  });
  const [validationErrors, setValidationErrors] = useState({});

  // Load content data when component mounts or contentId changes
  useEffect(() => {
    if (contentId) {
      loadContent(contentId);
      loadContentPermissions(contentId);
      loadVersionHistory(contentId);
    } else {
      // New content
      setContent({
        id: null,
        type: contentType,
        body: '',
        meta: { ...meta }
      });
      setIsLoading(false);
    }

    // Load the editor
    import('quill').then(Quill => {
      const quill = new Quill('#advanced-editor', DEFAULT_EDITOR_CONFIG);
      setEditor(quill);
      
      // Track changes for history
      quill.on('text-change', (delta, oldDelta, source) => {
        if (source === 'user') {
          // Add to history for undo/redo functionality
          const newHistory = history.slice(0, historyIndex + 1);
          newHistory.push({
            content: quill.getContents(),
            meta: { ...meta }
          });
          setHistory(newHistory);
          setHistoryIndex(newHistory.length - 1);
        }
      });
    });

    return () => {
      // Cleanup editor when component unmounts
      if (editor) {
        editor.off('text-change');
      }
    };
  }, [contentId]);

  /**
   * Load content data from API
   * @param {string} id Content ID
   */
  const loadContent = async (id) => {
    try {
      setIsLoading(true);
      const response = await axios.get(`${API_ENDPOINT}/${id}`);
      setContent(response.data);
      setMeta(response.data.meta || meta);
      
      // Initialize history with current content
      if (editor) {
        editor.setContents(response.data.body);
        setHistory([{
          content: editor.getContents(),
          meta: response.data.meta
        }]);
        setHistoryIndex(0);
      }
      
      setIsLoading(false);
    } catch (error) {
      console.error('Error loading content:', error);
      toast.error('Failed to load content. Please try again.');
      setIsLoading(false);
    }
  };

  /**
   * Load user permissions for this content
   * @param {string} id Content ID
   */
  const loadContentPermissions = async (id) => {
    try {
      const response = await axios.get(`${API_ENDPOINT}/${id}/permissions`);
      setPermissions(response.data);
    } catch (error) {
      console.error('Error loading permissions:', error);
      // Use default restricted permissions
    }
  };

  /**
   * Load version history for the content
   * @param {string} id Content ID
   */
  const loadVersionHistory = async (id) => {
    try {
      const response = await axios.get(`${API_ENDPOINT}/${id}/versions`);
      setVersionInfo(response.data);
    } catch (error) {
      console.error('Error loading version history:', error);
    }
  };

  /**
   * Handle meta data changes
   * @param {Object} e Event object
   */
  const handleMetaChange = (e) => {
    const { name, value } = e.target;
    setMeta({
      ...meta,
      [name]: value
    });
    
    // Clear validation error when field is updated
    if (validationErrors[name]) {
      const newErrors = { ...validationErrors };
      delete newErrors[name];
      setValidationErrors(newErrors);
    }
  };

  /**
   * Handle tag changes
   * @param {Array} tags New tags array
   */
  const handleTagsChange = (tags) => {
    setMeta({
      ...meta,
      tags
    });
  };

  /**
   * Validate content before saving
   * @returns {boolean} True if content is valid
   */
  const validateContent = () => {
    const errors = {};
    
    if (!meta.title.trim()) {
      errors.title = 'Title is required';
    }
    
    if (!meta.slug.trim()) {
      errors.slug = 'Slug is required';
    } else if (!/^[a-z0-9-]+$/.test(meta.slug)) {
      errors.slug = 'Slug can only contain lowercase letters, numbers, and hyphens';
    }
    
    if (meta.status === 'publish' && !meta.publishDate) {
      errors.publishDate = 'Publish date is required for published content';
    }
    
    if (editor && editor.getLength() <= 1) { // Empty editor has length 1 (just a newline)
      errors.body = 'Content cannot be empty';
    }
    
    setValidationErrors(errors);
    return Object.keys(errors).length === 0;
  };

  /**
   * Save content to API
   * @param {string} saveType Type of save (draft, publish, version)
   */
  const saveContent = async (saveType = 'draft') => {
    if (!validateContent()) {
      toast.error('Please fix the errors before saving');
      return;
    }
    
    try {
      setIsSaving(true);
      
      const contentData = {
        id: content.id,
        type: contentType,
        body: editor ? editor.getContents() : content.body,
        meta: { ...meta },
        saveType
      };
      
      let response;
      if (content.id) {
        response = await axios.put(`${API_ENDPOINT}/${content.id}`, contentData);
      } else {
        response = await axios.post(API_ENDPOINT, contentData);
      }
      
      setContent(response.data);
      
      // Show success message
      if (saveType === 'publish') {
        toast.success('Content published successfully!');
      } else if (saveType === 'version') {
        toast.success('New version created!');
        loadVersionHistory(response.data.id);
      } else {
        toast.success('Content saved as draft');
      }
      
      if (onSave) {
        onSave(response.data);
      }
      
      setIsSaving(false);
    } catch (error) {
      console.error('Error saving content:', error);
      toast.error(error.response?.data?.message || 'Failed to save content. Please try again.');
      setIsSaving(false);
    }
  };

  /**
   * Load a specific version of the content
   * @param {string} versionId Version ID to load
   */
  const loadVersion = async (versionId) => {
    try {
      setIsLoading(true);
      const response = await axios.get(`${API_ENDPOINT}/${content.id}/versions/${versionId}`);
      
      // Update editor with version content
      if (editor) {
        editor.setContents(response.data.body);
      }
      
      setMeta(response.data.meta);
      setSelectedVersion(versionId);
      setIsLoading(false);
      
      toast.info('Loaded version from ' + new Date(response.data.createdAt).toLocaleString());
    } catch (error) {
      console.error('Error loading version:', error);
      toast.error('Failed to load version');
      setIsLoading(false);
    }
  };

  /**
   * Undo last change
   */
  const handleUndo = () => {
    if (historyIndex > 0) {
      const prevState = history[historyIndex - 1];
      setHistoryIndex(historyIndex - 1);
      
      if (editor && prevState) {
        editor.setContents(prevState.content);
        setMeta(prevState.meta);
      }
    }
  };

  /**
   * Redo last undone change
   */
  const handleRedo = () => {
    if (historyIndex < history.length - 1) {
      const nextState = history[historyIndex + 1];
      setHistoryIndex(historyIndex + 1);
      
      if (editor && nextState) {
        editor.setContents(nextState.content);
        setMeta(nextState.meta);
      }
    }
  };

  /**
   * Delete content with confirmation
   */
  const handleDelete = async () => {
    if (!permissions.canDelete) {
      toast.error('You do not have permission to delete this content');
      return;
    }
    
    if (!window.confirm('Are you sure you want to delete this content? This action cannot be undone.')) {
      return;
    }
    
    try {
      await axios.delete(`${API_ENDPOINT}/${content.id}`);
      toast.success('Content deleted successfully');
      if (onCancel) {
        onCancel();
      }
    } catch (error) {
      console.error('Error deleting content:', error);
      toast.error('Failed to delete content');
    }
  };

  /**
   * Handle featured image upload
   * @param {Object} e Event object
   */
  const handleImageUpload = async (e) => {
    const file = e.target.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('image', file);
    
    try {
      const response = await axios.post('/api/admin/upload', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
      
      setMeta({
        ...meta,
        featuredImage: response.data.url
      });
      
      toast.success('Image uploaded successfully');
    } catch (error) {
      console.error('Error uploading image:', error);
      toast.error('Failed to upload image');
    }
  };

  /**
   * Generate slug from title
   */
  const generateSlug = () => {
    if (meta.title) {
      const slug = meta.title
        .toLowerCase()
        .replace(/[^\w\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim();
      
      setMeta({
        ...meta,
        slug
      });
    }
  };

  if (isLoading) {
    return <div className="loading-spinner">Loading content...</div>;
  }

  return (
    <div className="advanced-editor-container">
      <div className="editor-toolbar">
        <div className="editor-actions">
          <button 
            onClick={() => saveContent('draft')} 
            disabled={isSaving}
            className="btn btn-secondary"
          >
            Save Draft
          </button>
          
          {permissions.canPublish && (
            <button 
              onClick={() => saveContent('publish')} 
              disabled={isSaving}
              className="btn btn-primary"
            >
              Publish
            </button>
          )}
          
          {permissions.canCreateVersion && content.id && (
            <button 
              onClick={() => saveContent('version')} 
              disabled={isSaving}
              className="btn btn-outline"
            >
              Save as Version
            </button>
          )}
          
          {permissions.canDelete && content.id && (
            <button 
              onClick={handleDelete} 
              disabled={isSaving}
              className="btn btn-danger"
            >
              Delete
            </button>
          )}
          
          <button 
            onClick={onCancel} 
            disabled={isSaving}
            className="btn btn-link"
          >
            Cancel
          </button>
        </div>
        
        <div className="editor-history-actions">
          <button 
            onClick={handleUndo} 
            disabled={historyIndex <= 0 || isSaving}
            className="btn btn-icon"
            title="Undo"
          >
            <i className="icon-undo"></i>
          </button>
          
          <button 
            onClick={handleRedo} 
            disabled={historyIndex >= history.length - 1 || isSaving}
            className="btn btn-icon"
            title="Redo"
          >
            <i className="icon-redo"></i>
          </button>
        </div>
      </div>
      
      <div className="editor-meta-section">
        <div className="form-group">
          <label htmlFor="title">Title</label>
          <input
            type="text"
            id="title"
            name="title"
            value={meta.title}
            onChange={handleMetaChange}
            className={validationErrors.title ? 'form-control is-invalid' : 'form-control'}
          />
          {validationErrors.title && (
            <div className="invalid-feedback">{validationErrors.title}</div>
          )}
        </div>
        
        <div className="form-group slug-group">
          <label htmlFor="slug">Slug</label>
          <div className="input-group">
            <input
              type="text"
              id="slug"
              name="slug"
              value={meta.slug}
              onChange={handleMetaChange}
              className={validationErrors.slug ? 'form-control is-invalid' : 'form-control'}
            />
            <button 
              type="button" 
              onClick={generateSlug} 
              className="btn btn-outline-secondary"
              title="Generate from title"
            >
              <i className="icon-refresh"></i>
            </button>
          </div>
          {validationErrors.slug && (
            <div className="invalid-feedback">{validationErrors.slug}</div>
          )}
        </div>
        
        <div className="form-row">
          <div className="form-group col-md-6">
            <label htmlFor="status">Status</label>
            <select
              id="status"
              name="status"
              value={meta.status}
              onChange={handleMetaChange}
              className="form-control"
              disabled={!permissions.canPublish}
            >
              <option value="draft">Draft</option>
              <option value="publish">Published</option>
              <option value="scheduled">Scheduled</option>
              <option value="private">Private</option>
            </select>
          </div>
          
          <div className="form-group col-md-6">
            <label htmlFor="publishDate">Publish Date</label>
            <input
              type="datetime-local"
              id="publishDate"
              name="publishDate"
              value={meta.publishDate || ''}
              onChange={handleMetaChange}
              className={validationErrors.publishDate ? 'form-control is-invalid' : 'form-control'}
              disabled={meta.status !== 'scheduled' && meta.status !== 'publish'}
            />
            {validationErrors.publishDate && (
              <div className="invalid-feedback">{validationErrors.publishDate}</div>
            )}
          </div>
        </div>
        
        <div className="form-group">
          <label htmlFor="description">Meta Description</label>
          <textarea
            id="description"
            name="description"
            value={meta.description}
            onChange={handleMetaChange}
            className="form-control"
            rows="2"
          ></textarea>
          <small className="form-text text-muted">
            Brief description for search engines. Recommended length: 150-160 characters.
          </small>
        </div>
        
        <div className="form-group">
          <label>Featured Image</label>
          <div className="featured-image-container">
            {meta.featuredImage ? (
              <div className="current-image">
                <img src={meta.featuredImage} alt="Featured" />
                <button 
                  type="button" 
                  className="btn btn-sm btn-danger image-remove"
                  onClick={() => setMeta({...meta, featuredImage: ''})}
                >
                  <i className="icon-trash"></i>
                </button>
              </div>
            ) : (
              <div className="image-upload-placeholder">
                <input
                  type="file"
                  id="imageUpload"
                  accept="image/*"
                  onChange={handleImageUpload}
                  style={{ display: 'none' }}
                />
                <label htmlFor="imageUpload" className="btn btn-outline-secondary">
                  <i className="icon-image"></i> Select Image
                </label>
              </div>
            )}
          </div>
        </div>
        
        <div className="form-group">
          <label>Tags</label>
          <div className="tags-input-container">
            {/* Placeholder for tag input component */}
            <input
              type="text"
              placeholder="Add tags..."
              className="form-control"
            />
          </div>
        </div>
      </div>
      
      <div className="editor-content-section">
        <div id="advanced-editor" className={validationErrors.body ? 'editor-container is-invalid' : 'editor-container'}>
          {/* Quill editor will be mounted here */}
        </div>
        {validationErrors.body && (
          <div className="invalid-feedback">{validationErrors.body}</div>
        )}
      </div>
      
      {content.id && versionInfo.length > 0 && (
        <div className="editor-versions-section">
          <h3>Version History</h3>
          <div className="versions-list">
            {versionInfo.map(version => (
              <div 
                key={version.id} 
                className={`version-item ${selectedVersion === version.id ? 'active' : ''}`}
                onClick={() => loadVersion(version.id)}
              >
                <div className="version-date">
                  {new Date(version.createdAt).toLocaleString()}
                </div>
                <div className="version-author">{version.author}</div>
                <div className="version-type">{version.type}</div>
              </div>
            ))}
          </div>
        </div>
      )}
      
      {isSaving && (
        <div className="saving-overlay">
          <div className="spinner"></div>
          <p>Saving changes...</p>
        </div>
      )}
    </div>
  );
};

/**
 * Export default function to initialize the advanced editor
 * @param {Object} config Configuration options
 */
export default function initAdvancedEditor(config = {}) {
  // Merge default config with provided config
  const editorConfig = {
    ...DEFAULT_EDITOR_CONFIG,
    ...config
  };
  
  // Initialize event listeners and other setup
  document.addEventListener('DOMContentLoaded', () => {
    const editorContainers = document.querySelectorAll('[data-editor="advanced"]');
    
    editorContainers.forEach(container => {
      const contentId = container.dataset.contentId;
      const contentType = container.dataset.contentType;
      
      const root = document.createElement('div');
      container.appendChild(root);
      
      // Render the editor (assuming React is available)
      if (typeof ReactDOM !== 'undefined') {
        ReactDOM.render(
          React.createElement(AdvancedEditor, {
            contentId,
            contentType,
            onSave: (data) => {
              // Trigger custom event when content is saved
              const event = new CustomEvent('content:saved', { detail: data });
              document.dispatchEvent(event);
            },
            onCancel: () => {
              // Trigger custom event when editing is cancelled
              const event = new CustomEvent('content:cancelled');
              document.dispatchEvent(event);
            }
          }),
          root
        );
      } else {
        console.error('ReactDOM is not available. Make sure React is loaded before initializing the editor.');
      }
    });
  });
  
  // Return public API
  return {
    getConfig: () => editorConfig,
    updateConfig: (newConfig) => {
      Object.assign(editorConfig, newConfig);
    }
  };
}

// Additional utility functions for content manipulation

/**
 * Sanitize HTML content
 * @param {string} html HTML content to sanitize
 * @returns {string} Sanitized HTML
 */
export function sanitizeContent(html) {
  // Simple sanitization
  return html
    .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
    .replace(/on\w+="[^"]*"/g, '')
    .replace(/on\w+='[^']*'/g, '');
}

/**
 * Extract plain text from content delta
 * @param {Object} delta Quill delta object
 * @returns {string} Plain text
 */
export function extractPlainText(delta) {
  if (!delta || !delta.ops) return '';
  
  return delta.ops.reduce((text, op) => {
    if (typeof op.insert === 'string') {
      return text + op.insert;
    }
    return text;
  }, '');
}

/**
 * Generate auto-save key for a specific content
 * @param {string} contentId Content ID
 * @returns {string} Auto-save key
 */
export function generateAutoSaveKey(contentId) {
  return `content_autosave_${contentId || 'new'}`;
}

/**
 * Check if the user has required permissions
 * @param {Object} userPermissions User permission object
 * @param {Array} requiredPermissions Array of required permission keys
 * @returns {boolean} True if user has all required permissions
 */
export function hasPermissions(userPermissions, requiredPermissions) {
  return requiredPermissions.every(permission => userPermissions[permission]);
}