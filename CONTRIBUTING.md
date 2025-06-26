# Contributing to Esistenze WordPress Kit

Thank you for considering contributing to Esistenze WordPress Kit! This document provides guidelines for contributing to the project.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Making Changes](#making-changes)
- [Testing](#testing)
- [Submitting Changes](#submitting-changes)
- [Coding Standards](#coding-standards)
- [Translation](#translation)

## Code of Conduct

This project adheres to a code of conduct. By participating, you are expected to uphold this code. Please report unacceptable behavior to [info@esistenze.com](mailto:info@esistenze.com).

## Getting Started

1. Fork the repository on GitHub
2. Clone your fork locally
3. Create a new branch for your feature or bug fix
4. Make your changes
5. Test your changes
6. Submit a pull request

## Development Setup

### Prerequisites

- PHP 7.4 or higher
- WordPress 5.8 or higher
- Node.js 14.0 or higher
- Composer

### Installation

1. Clone the repository:
```bash
git clone https://github.com/esistenze/wordpress-kit.git
cd wordpress-kit
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install Node.js dependencies:
```bash
npm install
```

4. Build assets:
```bash
npm run build
```

## Making Changes

### Branch Naming

Use descriptive branch names:
- `feature/add-new-button-type`
- `fix/permission-error`
- `docs/update-readme`

### Commit Messages

Write clear, descriptive commit messages:
- Use present tense ("Add feature" not "Added feature")
- Use imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit the first line to 72 characters or less
- Reference issues and pull requests liberally

Example:
```
Add WhatsApp button type to Smart Buttons

- Implement WhatsApp URL generation
- Add WhatsApp icon to button types
- Update admin interface for WhatsApp options
- Add tests for WhatsApp functionality

Fixes #123
```

## Testing

### Running Tests

```bash
# Run PHP tests
composer test

# Run JavaScript tests
npm test

# Run code style checks
composer phpcs
npm run lint:js
npm run lint:css
```

### Writing Tests

- Write unit tests for new functionality
- Ensure all tests pass before submitting
- Aim for good test coverage
- Test both success and failure scenarios

## Submitting Changes

1. Push your changes to your fork
2. Create a pull request against the main branch
3. Provide a clear description of the changes
4. Link to any relevant issues
5. Ensure all tests pass
6. Wait for code review

### Pull Request Template

```markdown
## Description
Brief description of the changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Tests pass locally
- [ ] New tests added for new functionality
- [ ] Manual testing completed

## Checklist
- [ ] Code follows project style guidelines
- [ ] Self-review completed
- [ ] Comments added for complex code
- [ ] Documentation updated
- [ ] No breaking changes (or clearly documented)
```

## Coding Standards

### PHP

- Follow WordPress Coding Standards
- Use PSR-4 autoloading
- Add PHPDoc comments for all public methods
- Use type hints where possible
- Sanitize all user input
- Escape all output

### JavaScript

- Follow WordPress JavaScript Coding Standards
- Use ES6+ features
- Add JSDoc comments for functions
- Use meaningful variable names
- Handle errors gracefully

### CSS

- Follow WordPress CSS Coding Standards
- Use BEM methodology for class naming
- Mobile-first responsive design
- Use CSS custom properties for theming

### HTML

- Use semantic HTML5 elements
- Ensure accessibility (WCAG 2.1 AA)
- Validate HTML markup
- Use proper heading hierarchy

## Translation

### Adding Translatable Strings

1. Wrap strings in translation functions:
```php
__('Text to translate', 'esistenze-wp-kit')
_e('Text to translate', 'esistenze-wp-kit')
```

2. Update POT file:
```bash
npm run build:pot
```

3. Add translations to PO files in `/languages/`

### Translation Guidelines

- Use clear, concise language
- Provide context for translators
- Avoid technical jargon
- Test translations in context

## Documentation

- Update README.md for new features
- Add inline code comments
- Update CHANGELOG.md
- Include examples in documentation

## Questions?

If you have questions about contributing, please:

1. Check existing issues and discussions
2. Create a new issue with the "question" label
3. Contact us at [info@esistenze.com](mailto:info@esistenze.com)

Thank you for contributing to Esistenze WordPress Kit! 