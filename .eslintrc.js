module.exports = {
    extends: [
        '@wordpress/eslint-plugin/recommended'
    ],
    env: {
        browser: true,
        es6: true,
        node: true,
        jquery: true
    },
    globals: {
        wp: 'readonly',
        ajaxurl: 'readonly',
        jQuery: 'readonly',
        $: 'readonly'
    },
    rules: {
        'no-console': 'warn',
        'no-unused-vars': 'warn',
        'prefer-const': 'error',
        'no-var': 'error',
        'eqeqeq': 'error',
        'curly': 'error',
        'brace-style': ['error', '1tbs'],
        'comma-dangle': ['error', 'never'],
        'indent': ['error', 4],
        'quotes': ['error', 'single'],
        'semi': ['error', 'always']
    },
    parserOptions: {
        ecmaVersion: 2020,
        sourceType: 'module'
    }
}; 