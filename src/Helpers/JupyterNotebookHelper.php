<?php

namespace Nacho\Helpers;

use Exception;
use Nacho\Contracts\AlternativeContentHelper;
use Nacho\Nacho;
use Psr\Log\LoggerInterface;

class JupyterNotebookHelper implements AlternativeContentHelper
{
    private LoggerInterface $logger;
    private bool $debug;

    public function __construct(LoggerInterface $logger, bool $debug)
    {
        $this->logger = $logger;
        $this->debug = $debug;
    }

    public function getContent(string $notebookPath): string
    {
        /* try { */
        /*     return $this->convertJupyterNotebookJupytext(...$args); */
        /* } catch (Exception $e) { */
        /*     return $this->convertJupyterNotebookNative(...$args); */
        /* } */
        return $this->convertJupyterNotebookNative($notebookPath);
    }

    # https://nbformat.readthedocs.io/en/latest/format_description.html#top-level-structure
    private function convertJupyterNotebookNative(string $notebookPath): string
    {
        $content = json_decode(file_get_contents($notebookPath), true);

        $output = "";

        if (isset($content['cells'])) {
            foreach ($content['cells'] as $cell) {
                $output .= $this->processCell($cell);
            }
        }

        return ltrim($output);
    }

    private function processCell(array $cell): string
    {
        $output = "";

        if (isset($cell['cell_type']) && isset($cell['source'])) {
            if ($cell['cell_type'] === 'code') {
                $this->processCodeCell($cell, $output);
            } else if ($cell['cell_type'] === 'raw') {
                $output .= "\n```\n" . implode("", $cell['source']) . "\n```\n";
            } else {
                $this->processPlainCell($cell, $output);
            }
        }

        return $output;
    }

    private function processCodeCell(array $cell, string &$output)
    {
        $language = $this->getCellCodeLanguage($cell);
        $output .= "\n";
        if ($language === 'python') {
            $output .= "```$language\n";
            $this->processPlainCell($cell, $output);
            $output .= "```\n";
        } else {
            $output .= implode("\n", array_slice($cell['source'], 1)) . "\n";
        }

        if (isset($cell['outputs']) && !in_array($language, ['latex', 'html'])) {
            foreach ($cell['outputs'] as $cellOutput) {
                switch ($cellOutput['output_type']) {
                    case 'stream':
                        $streamContent = is_array($cellOutput['text']) ? implode("\n", $cellOutput['text']) : $cellOutput['text'];
                        $output .= sprintf("```\n%s\n```\n", $streamContent);
                        break;
                    case 'display_data':
                    case 'execute_result':
                        foreach ($cellOutput['data'] as $mime => $dataContent) {
                            if (is_array($dataContent)) {
                                $dataContent = implode("\n", $dataContent);
                            }
                            $this->processOutputData($mime, $dataContent, $output);
                        }
                        break;
                    case 'error':
                        $output .= sprintf("```\n%s: %s\n```\n", $cellOutput['ename'], $cellOutput['evalue']);
                        break;
                }
            }
        }
    }

    private function getCellCodeLanguage(array $cell): string
    {
        if (isset($cell['source'])) {
            if (is_string($cell['source'])) {
                $source = explode("\n", $cell['source']);
            } else {
                $source = $cell['source'];
            }
            if (count($source) > 0) {
                $re = '/%%(.+)/m';
                $str = $source[0];
                preg_match($re, $str, $matches);
                if (count($matches) >= 2 && strlen($matches[1]) > 0) {
                    return $matches[1];
                }
            }

            return 'python';
        } else {
            return 'python';
        }
    }

    private function processOutputData(string $mime, string $dataContent, string &$output)
    {
        switch ($mime) {
            case "text/plain":
                $output .= sprintf("```\n%s\n```\n", $dataContent);
                break;
            case "image/png":
                $output .= sprintf("![base64 image](data:image/png;base64,%s)\n", rtrim($dataContent));
                break;
            case "text/latex":
            case "text/html":
                break;
            default:
                // print($mime . "\n");
                // var_dump($dataContent);
                break;
        }
    }

    private function processPlainCell(array $cell, string &$output)
    {
        if (is_array($cell['source'])) {
            foreach ($cell['source'] as $line) {
                $output .= $line;
            }
            $output .= "\n";
        } else {
            $output .= $cell['source'] . "\n";
        }
    }

    private function convertJupyterNotebookJupytext(string $notebookPath, bool $debug = false): string
    {
        ob_start();
        $out = system("which jupytext");
        ob_end_clean();

        if (!is_string($out) || strlen($out) === 0) {
            throw new Exception("Unable to find jupytext executable");
        }

        $cmd = ["jupytext", "--to", "markdown", "-o", "-", $notebookPath];

        ob_start();
        $out = system(implode(' ', $cmd));
        $content = ob_get_contents();
        ob_end_clean();

        if (!is_string($out)) {
            throw new Exception("Unable to convert notebook using jupytext");
        }

        return $content;
    }
}
