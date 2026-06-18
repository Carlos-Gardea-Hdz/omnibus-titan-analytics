import js from '@eslint/js';
import globals from 'globals';
import reactHooks from 'eslint-plugin-react-hooks';
import reactRefresh from 'eslint-plugin-react-refresh';
import tseslint from 'typescript-eslint';

export default tseslint.config(
    { ignores: ['public', 'vendor', 'node_modules', 'bootstrap/ssr', 'resources/js/types/generated.d.ts'] },
    {
        extends: [js.configs.recommended, ...tseslint.configs.recommended],
        files: ['resources/js/**/*.{ts,tsx}'],
        languageOptions: {
            ecmaVersion: 2022,
            globals: globals.browser,
        },
        plugins: {
            'react-hooks': reactHooks,
            'react-refresh': reactRefresh,
        },
        rules: {
            ...reactHooks.configs.recommended.rules,
            'react-refresh/only-export-components': ['warn', { allowConstantExport: true }],
            '@typescript-eslint/no-explicit-any': 'error',
            '@typescript-eslint/consistent-type-imports': 'warn',
        },
    },
    {
        // Context modules intentionally co-locate the Provider with its hook —
        // a standard, safe React pattern. Fast-refresh granularity is not a concern here.
        files: ['resources/js/lib/**/*.tsx'],
        rules: {
            'react-refresh/only-export-components': 'off',
        },
    },
);
