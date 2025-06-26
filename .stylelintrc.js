module.exports = {
    extends: [
        'stylelint-config-wordpress'
    ],
    rules: {
        'indentation': 4,
        'color-hex-case': 'lower',
        'color-hex-length': 'short',
        'declaration-colon-space-after': 'always',
        'declaration-colon-space-before': 'never',
        'function-comma-space-after': 'always',
        'function-comma-space-before': 'never',
        'number-leading-zero': 'always',
        'number-no-trailing-zeros': true,
        'string-quotes': 'single',
        'unit-case': 'lower',
        'value-list-comma-space-after': 'always',
        'value-list-comma-space-before': 'never',
        'property-case': 'lower',
        'selector-pseudo-class-case': 'lower',
        'selector-pseudo-element-case': 'lower',
        'selector-type-case': 'lower',
        'media-feature-name-case': 'lower',
        'at-rule-name-case': 'lower',
        'comment-whitespace-inside': 'always'
    }
}; 