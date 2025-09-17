<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class codeAssist extends Controller
{
    public function codeAssist(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string',
                'language' => 'required|string',
                'action' => 'required|in:improve,explain,debug,complete',
                'context' => 'sometimes|array'
            ]);

            $code = $request->input('code');
            $language = $request->input('language');
            $action = $request->input('action');
            $context = $request->input('context', []);

            $suggestions = $this->generateCodeSuggestions($code, $language, $action, $context);

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions,
                'action' => $action,
                'language' => $language
            ]);
        } catch (\Exception $e) {
            Log::error('AI Code Assist Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'AI service temporarily unavailable'
            ], 500);
        }
    }

    private function generateCodeSuggestions($code, $language, $action, $context)
    {
        // For now, we'll provide rule-based suggestions
        // You can integrate with OpenAI, Claude, or other AI services here

        switch ($action) {
            case 'complete':
                return $this->getCompletionSuggestions($code, $language);

            case 'improve':
                return $this->getImprovementSuggestions($code, $language);

            case 'debug':
                return $this->getDebugSuggestions($code, $language);

            case 'explain':
                return $this->getExplanationSuggestions($code, $language);

            default:
                return [];
        }
    }

    private function getCompletionSuggestions($code, $language)
    {
        $suggestions = [];

        switch ($language) {
            case 'javascript':
                if (strpos($code, 'function') !== false && strpos($code, '{') !== false && strpos($code, '}') === false) {
                    $suggestions[] = [
                        'title' => 'Complete Function Body',
                        'description' => 'Add a return statement and close the function',
                        'code' => "    // TODO: Implement function logic\n    return null;\n}",
                        'insertAt' => 'cursor'
                    ];
                }

                if (strpos($code, 'console.') !== false) {
                    $suggestions[] = [
                        'title' => 'Console Methods',
                        'description' => 'Common console methods',
                        'code' => 'console.log();\nconsole.error();\nconsole.warn();',
                        'insertAt' => 'cursor'
                    ];
                }
                break;

            case 'html':
                if (strpos($code, '<div') !== false && strpos($code, '</div>') === false) {
                    $suggestions[] = [
                        'title' => 'Close DIV Tag',
                        'description' => 'Add closing div tag',
                        'code' => '</div>',
                        'insertAt' => 'cursor'
                    ];
                }

                if (strpos($code, '<!DOCTYPE') === false && strpos($code, '<html') === false) {
                    $suggestions[] = [
                        'title' => 'HTML Boilerplate',
                        'description' => 'Add HTML document structure',
                        'code' => "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Document</title>\n</head>\n<body>\n    \n</body>\n</html>",
                        'insertAt' => 'replace'
                    ];
                }
                break;

            case 'css':
                if (strpos($code, '{') !== false && strpos($code, '}') === false) {
                    $suggestions[] = [
                        'title' => 'Close CSS Rule',
                        'description' => 'Add closing brace for CSS rule',
                        'code' => '}',
                        'insertAt' => 'cursor'
                    ];
                }
                break;

            case 'python':
                if (strpos($code, 'def ') !== false && strpos($code, ':') !== false) {
                    $suggestions[] = [
                        'title' => 'Function Body',
                        'description' => 'Add function implementation',
                        'code' => "    # TODO: Implement function\n    pass",
                        'insertAt' => 'cursor'
                    ];
                }
                break;
        }

        return $suggestions;
    }

    private function getImprovementSuggestions($code, $language)
    {
        $suggestions = [];

        switch ($language) {
            case 'javascript':
                // Check for var usage
                if (strpos($code, 'var ') !== false) {
                    $improvedCode = str_replace('var ', 'const ', $code);
                    $suggestions[] = [
                        'title' => 'Use const/let instead of var',
                        'description' => 'Modern JavaScript uses const and let for better scoping',
                        'code' => $improvedCode,
                        'insertAt' => 'replace'
                    ];
                }

                // Check for == usage
                if (strpos($code, '==') !== false && strpos($code, '===') === false) {
                    $improvedCode = str_replace(' == ', ' === ', $code);
                    $suggestions[] = [
                        'title' => 'Use strict equality (===)',
                        'description' => 'Strict equality prevents type coercion issues',
                        'code' => $improvedCode,
                        'insertAt' => 'replace'
                    ];
                }

                // Add error handling
                if (strpos($code, 'async') !== false && strpos($code, 'try') === false) {
                    $suggestions[] = [
                        'title' => 'Add Error Handling',
                        'description' => 'Wrap async code in try-catch block',
                        'code' => "try {\n    " . str_replace("\n", "\n    ", trim($code)) . "\n} catch (error) {\n    console.error('Error:', error);\n}",
                        'insertAt' => 'replace'
                    ];
                }
                break;

            case 'html':
                // Add semantic tags
                if (strpos($code, '<div') !== false && strpos($code, '<main') === false) {
                    $suggestions[] = [
                        'title' => 'Use Semantic HTML',
                        'description' => 'Replace div with semantic tags',
                        'code' => str_replace('<div', '<main', str_replace('</div>', '</main>', $code)),
                        'insertAt' => 'replace'
                    ];
                }

                // Add alt attributes to images
                if (strpos($code, '<img') !== false && strpos($code, 'alt=') === false) {
                    $improvedCode = str_replace('<img', '<img alt="Description"', $code);
                    $suggestions[] = [
                        'title' => 'Add Alt Attribute',
                        'description' => 'Images should have alt attributes for accessibility',
                        'code' => $improvedCode,
                        'insertAt' => 'replace'
                    ];
                }
                break;

            case 'css':
                // Use shorthand properties
                if (strpos($code, 'border-top-width') !== false && strpos($code, 'border-top-style') !== false) {
                    $suggestions[] = [
                        'title' => 'Use Shorthand Properties',
                        'description' => 'Combine multiple border properties into a single shorthand property',
                        'code' => "border: 1px solid #000;",
                        'insertAt' => 'replace'
                    ];
                }
                
                // Add vendor prefixes
                if (strpos($code, 'transition:') !== false && strpos($code, '-webkit-') === false) {
                    $suggestions[] = [
                        'title' => 'Add Vendor Prefixes',
                        'description' => 'Add -webkit- prefix for wider browser compatibility',
                        'code' => "-webkit-transition: all 0.5s ease-in-out;\n" . $code,
                        'insertAt' => 'replace'
                    ];
                }
                break;

            case 'python':
                // Use f-strings for string formatting
                if (strpos($code, '.format(') !== false && strpos($code, 'f"') === false) {
                    $suggestions[] = [
                        'title' => 'Use f-strings',
                        'description' => 'f-strings are more readable and faster than .format()',
                        'code' => "print(f'Hello, {name}!')", // Example replacement
                        'insertAt' => 'replace'
                    ];
                }

                // Use context managers for file I/O
                if (strpos($code, 'open(') !== false && strpos($code, 'with open(') === false) {
                    $suggestions[] = [
                        'title' => 'Use a Context Manager',
                        'description' => 'Use `with open(...)` to ensure files are properly closed',
                        'code' => "with open('filename.txt', 'r') as f:\n    content = f.read()",
                        'insertAt' => 'replace'
                    ];
                }
                break;
        }

        return $suggestions;
    }

    private function getDebugSuggestions($code, $language)
    {
        $suggestions = [];
        // This is a basic rule-based debugger.
        // A real-world implementation would require a much more sophisticated logic or AI model.

        switch ($language) {
            case 'javascript':
                if (strpos($code, 'document.getElementById') !== false && strpos($code, '.innerHTML') === false) {
                    $suggestions[] = [
                        'title' => 'Check for Null Elements',
                        'description' => 'Ensure the element exists before accessing its properties. You can add a check like `if (element) { ... }`',
                        'code' => "const element = document.getElementById('someId');\nif (element) {\n    // your code here\n}",
                        'insertAt' => 'replace'
                    ];
                }
                break;

            case 'python':
                if (strpos($code, 'for') !== false && strpos($code, 'in range') === false) {
                    $suggestions[] = [
                        'title' => 'Common Loop Error',
                        'description' => 'Using a for loop without `range()` or an iterable can lead to a `TypeError`',
                        'code' => "for i in range(10):",
                        'insertAt' => 'replace'
                    ];
                }
                break;
        }

        if (empty($suggestions)) {
            $suggestions[] = [
                'title' => 'Review Your Code',
                'description' => 'I cannot find a specific bug based on my rules. Review your code for syntax errors, logical flaws, or typos.',
                'code' => '// No specific debug suggestion',
                'insertAt' => 'append'
            ];
        }

        return $suggestions;
    }

    private function getExplanationSuggestions($code, $language)
    {
        // This is a very basic placeholder.
        // A real AI would provide a detailed explanation of the code.

        switch ($language) {
            case 'javascript':
                $explanation = "This is a JavaScript code snippet. It likely performs client-side logic in a web browser, such as handling user interactions or manipulating the Document Object Model (DOM).";
                break;
            case 'html':
                $explanation = "This is an HTML snippet. It defines the structure of a web page using tags like `<div>`, `<h1>`, and `<body>`.";
                break;
            case 'css':
                $explanation = "This is a CSS snippet. It describes the presentation of a document written in HTML, including colors, fonts, and layout.";
                break;
            case 'python':
                $explanation = "This is a Python snippet. It's a versatile language used for web development, data analysis, and automation.";
                break;
            default:
                $explanation = "This is a code snippet. I can help analyze its structure and purpose.";
                break;
        }

        return [[
            'title' => 'Code Explanation',
            'description' => 'A high-level overview of the code.',
            'code' => $explanation,
            'insertAt' => 'none'
        ]];
    }
}