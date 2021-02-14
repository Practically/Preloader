module.exports = {
    trailingComma: 'all',
    tabWidth: 4,
    semi: true,
    singleQuote: true,
    bracketSpacing: false,
    endOfLine: 'lf',

    overrides: [
        {
            files: ['*.yml', '*.yaml'],
            options: {
                tabWidth: 2,
            },
        },
        {
            files: ['*.md'],
            options: {
                tabWidth: 2,
                proseWrap: 'always',
            },
        },
    ],
};
