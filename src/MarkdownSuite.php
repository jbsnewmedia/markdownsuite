<?php

declare(strict_types=1);

namespace JBSNewMedia\MarkdownSuite;

use League\CommonMark\CommonMarkConverter;

class MarkdownSuite
{

    protected string $directory = '';

    protected array $suiteData = [];

    protected array $suiteDataPart = [];

    protected array $contentData = [];

    protected CommonMarkConverter|\Parsedown|\ParsedownExtra|null $parser = null;

    protected string $parserSelected = '';

    public static string $version = '1.0.0';

    public function __construct(string $parser = 'commonmark')
    {
        $parser = strtolower($parser);
        switch ($parser) {
            case 'parsedown':
                $this->parser = new \Parsedown();
                $this->parserSelected = 'parsedown';
                break;
            case 'parsedownextra':
                $this->parser = new \ParsedownExtra();
                $this->parserSelected = 'parsedownextra';
                break;
            default:
                $this->parser = new CommonMarkConverter();
                $this->parserSelected = 'commonmark';
                break;
        }
    }

    static public function getVersion():string
    {
        return self::$version;
    }

    public function setDirectory(string $directory):self
    {
        $this->directory = $directory;

        return $this;
    }

    public function getDirectory():string
    {
        return $this->directory;
    }

    protected function parseContent(string $content):string
    {
        if ($this->parserSelected === 'commonmark') {
            return (string)$this->parser->convert($content);
        }

        if (($this->parserSelected === 'parsedown') || ($this->parserSelected === 'parsedownextra')) {
            return $this->parser->text($content);
        }

        return '';
    }

    protected function parseContentImages(string $content, string $path): string
    {
        $pattern = '/<img([^>]+)src=["\']?([^"\'>\s]+)["\']?/i';
        $matches = [];
        preg_match_all($pattern, $content, $matches);

        if (isset($matches[0])) {
            foreach ($matches[0] as $index => $fullMatch) {
                $imgAttributes = $matches[1][$index];
                $imgSrc = $matches[2][$index];

                if (strpos($imgAttributes, 'class=') !== false) {
                    $newImgTag = str_replace('class="', 'class="img-fluid responsive-img ', $fullMatch);
                } else {
                    $newImgTag = str_replace('<img', '<img class="img-fluid responsive-img"', $fullMatch);
                }

                $newImgTag = str_replace('src="' . $imgSrc . '"', 'src="' . $path . '/' . $imgSrc . '"', $newImgTag);

                $content = str_replace($fullMatch, $newImgTag, $content);
            }
        }

        return $content;
    }

    public function getParser():CommonMarkConverter|\Parsedown|\ParsedownExtra|null
    {
        return $this->parser;
    }

    public function getSuiteData():array
    {
        return $this->suiteData;
    }

    public function getSuiteDataPart():array
    {
        return $this->suiteDataPart;
    }

    public function getContentData():array
    {
        return $this->contentData;
    }

    public function scanDirectory(string $scanDir):void
    {
        $this->setDirectory($scanDir);
        $dirs = scandir($scanDir);
        $dirs = array_diff($dirs, ['.', '..']);
        ksort($dirs);
        foreach ($dirs as $dir) {
            $markdownFile = $scanDir.'/'.$dir.'/readme.md';
            if (file_exists($markdownFile)) {
                $fileContent = file_get_contents($markdownFile);
                $header = $this->parseFirstHashLine($fileContent);
                if ($header !== null) {
                    $this->suiteData[$this->parseUrlPath($dir)] = [
                        'key' => $this->parseUrlPath($dir),
                        'header' => $header,
                        'active' => false,
                        'path' => $scanDir.'/'.$dir,
                        'content' => $fileContent,
                        'content_parsed' => $this->parseContent($fileContent),
                        'content_sub' => $this->scanSubDirectory($scanDir.'/'.$dir, $this->parseUrlPath($dir)),
                    ];
                }
            }
        }
    }

    protected function scanSubDirectory(string $scanDir, string $key):array
    {
        $dirs = scandir($scanDir);
        $dirs = array_diff($dirs, ['.', '..']);
        ksort($dirs);
        $result = [];
        foreach ($dirs as $dir) {
            $markdownDir = $scanDir.'/'.$dir;
            if (is_dir($markdownDir)) {
                $markdownFile = $scanDir.'/'.$dir.'/readme.md';
                if (file_exists($markdownFile)) {
                    $fileContent = file_get_contents($markdownFile);
                    $header = $this->parseFirstHashLine($fileContent);
                    if ($header !== null) {
                        $urlKey = $this->parseUrlPath($dir);
                        if ($urlKey !== 'undefined') {
                            $result[$this->parseUrlPath($dir)] = [
                                'key' => $this->parseUrlPath($dir),
                                'parent_key' => $key,
                                'header' => $header,
                                'active' => false,
                                'path' => $scanDir.'/'.$dir,
                                'content' => $fileContent,
                                'content_parsed' => $this->parseContentImages($this->parseContent($fileContent), $key.'/'.$this->parseUrlPath($dir)),
                                'content_sub' => [],
                            ];
                        }
                    }
                }
            }
        }

        return $result;
    }

    protected function scanContentDirectory(array $data):array
    {
        $scanDir = $data['path'];
        $files = scandir($scanDir);
        $files = array_diff($files, ['.', '..', 'readme.md']);
        ksort($files);
        $result = [];
        foreach ($files as $file) {
            $markdownFile = $scanDir.'/'.$file;
            $fileWithoutExtension = pathinfo($file, PATHINFO_FILENAME);
            if ((file_exists($markdownFile))&&(!is_dir($markdownFile))) {
                $fileContent = file_get_contents($markdownFile);
                $headers = $this->parseHashLines($fileContent);
                if ($headers !== null) {
                    foreach ($headers as $header) {
                        $result[$this->parseUrlPath($fileWithoutExtension)] = [
                            'header' => $header['header'],
                            'key' => $this->parseUrlPath($fileWithoutExtension),
                            'path' => $scanDir.'/'.$fileWithoutExtension,
                            'anchor' => $header['anchor'],
                            'level' => $header['level'],
                            'content' => $header['content'],
                            'content_parsed' => $this->parseContentImages($header['content_parsed'], $data['parent_key'].'/'.$data['key'].'/'.$this->parseUrlPath($fileWithoutExtension)),
                            'content_sub' => $header['content_sub'],
                        ];
                        if ($result[$this->parseUrlPath($fileWithoutExtension)]['content_sub']!==[]) {
                            foreach ($result[$this->parseUrlPath($fileWithoutExtension)]['content_sub'] as $key => $values) {
                                $result[$this->parseUrlPath($fileWithoutExtension)]['content_sub'][$key]['content_parsed'] = $this->parseContentImages($values['content_parsed'], $data['parent_key'].'/'.$data['key'].'/'.$this->parseUrlPath($fileWithoutExtension));
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function parseFirstHashLine(string $content):?string
    {
        $matches = [];
        if (preg_match('/^\s*#.*$/m', $content, $matches)) {
            return trim(substr(trim($matches[0]), 1));
        }

        return null;
    }

    public function parseHashLines(string $content):?array
    {
        $matches = [];
        if (preg_match_all('/^\s*(###|##)\s*(.*)$/m', $content, $matches, PREG_OFFSET_CAPTURE)) {
            if ((!isset($matches[2])) || (count($matches[2]) === 0)) {
                return null;
            }

            $result = [];
            $headerCount = count($matches[0]);

            $lastId = 0;
            for ($i = 0; $i < $headerCount; $i++) {
                $headerLevel = $matches[1][$i][0];
                $headerText = trim($matches[2][$i][0]);
                $headerStart = (int)$matches[0][$i][1];

                $nextHeaderStart = $i < $headerCount - 1?$matches[0][$i + 1][1]:strlen($content);

                $headerContent = trim(substr($content, $headerStart, $nextHeaderStart - $headerStart));

                if ($headerLevel === '###') {
                    $headerLevel = 3;
                    if (isset($result[$lastId])) {
                        $result[$lastId]['content_sub'][$i] = [
                            'level' => $headerLevel,
                            'header' => $headerText,
                            'anchor' => $this->getAnchor($headerText),
                            'content' => $headerContent,
                            'content_parsed' => $this->parseContent($headerContent),
                        ];
                    }
                } else {
                    $headerLevel = 2;
                    $result[$i] = [
                        'level' => $headerLevel,
                        'header' => $headerText,
                        'anchor' => $this->getAnchor($headerText),
                        'content' => $headerContent,
                        'content_parsed' => $this->parseContent($headerContent),
                        'content_sub' => [],
                    ];
                    $lastId = $i;
                }
            }

            return $result;
        }

        return null;
    }


    public function parseUrlPath(string $content):?string
    {
        $content = explode('_', $content);
        if (count($content) <= 1) {
            return 'undefined';
        }
        array_shift($content);

        return strtolower(implode('_', $content));
    }

    public function setPath(string $path):void
    {
        if ($path === '') {
            $pathArray[0] = key($this->suiteData);
            if ((isset($this->suiteData[$pathArray[0]]['content_sub'])) && ($this->suiteData[$pathArray[0]]['content_sub']!==[])) {
                $pathArray[1]=key($this->suiteData[$pathArray[0]]['content_sub']);
            }
        } else {
            $pathArray = explode('/', $path);
        }
        if ((count($pathArray) === 1) || ((count($pathArray) === 2) || ($pathArray[1] === ''))) {
            if (!isset($this->suiteData[$pathArray[0]])) {
                $this->die404();
            }

            if ((!isset($this->suiteData[$pathArray[0]]['content_sub'])) || ($this->suiteData[$pathArray[0]]['content_sub']===[])) {
                $this->die404();
            }

            if (!isset($this->suiteData[$pathArray[0]]['content_sub'][$pathArray[1]])) {
                $this->die404();
            }
        }

        if (isset($this->suiteData[$pathArray[0]]['content_sub'][$pathArray[1]])) {
            $this->suiteData[$pathArray[0]]['active'] = true;
            $this->suiteData[$pathArray[0]]['content_sub'][$pathArray[1]]['active'] = true;
            $this->suiteDataPart = $this->suiteData[$pathArray[0]]['content_sub'][$pathArray[1]];
            $this->suiteDataPart['path'] = $this->suiteData[$pathArray[0]]['path'].'/'.$this->suiteDataPart['path'];
            $this->suiteDataPart['key'] = $this->suiteData[$pathArray[0]]['key'].'/'.$this->suiteDataPart['key'];
            $this->contentData = $this->scanContentDirectory(
                $this->suiteData[$pathArray[0]]['content_sub'][$pathArray[1]]
            );
        }
    }

    public function getAnchor(string $header):string
    {
        $header = strtolower($header);
        $header = str_replace(['ü', 'ö', 'ä', 'ß'], ['ue', 'oe', 'ae', 'ss'], $header);
        $header = preg_replace('/[^a-z0-9]/', '-', $header);
        $header = preg_replace('/-+/', '-', $header);
        $header = trim($header, '-');

        return $header;
    }

    public function isAllowedFile(string $path, array $allowedFiles):bool
    {
        $path = strtolower($path);
        $fileInfo = pathinfo($path);
        if (isset($fileInfo['extension'])) {
            if (in_array($fileInfo['extension'], $allowedFiles)) {
                return true;
            }
        }

        return false;
    }

    public function sendFile(string $path):void
    {
        if ($path === '') {
            $this->die404();
        }

        $pathArray = explode('/', $path);
        if ((count($pathArray) <= 2)||(count($pathArray) > 4)||(!isset($this->suiteData[$pathArray[0]]['content_sub'][$pathArray[1]]))) {
            $this->die404();
        }

        $dirDetails=$this->suiteData[$pathArray[0]]['content_sub'][$pathArray[1]];

        if (count($pathArray) === 3) {
            $file=$dirDetails['path'].'/readme/'.$pathArray[2];
            if (!file_exists($file)) {
                $this->die404();
            }

            $this->dieFile($file);
        }

        if (count($pathArray) === 4) {
            $fileDetails=$this->scanContentDirectory(
                $dirDetails
            );

            if (!isset($fileDetails[$pathArray[2]])) {
                $this->die404();
            }

            $file=$fileDetails[$pathArray[2]]['path'].'/'.$pathArray[3];
            if (!file_exists($file)) {
                $this->die404();
            }

            $this->dieFile($file);
        }

        $this->die404();
    }

    public function die404():void
    {
        header('HTTP/1.0 404 Not Found');
        die('404 Not Found');
    }

    public function dieFile(string $file):void
    {
        header('Content-Type: '.mime_content_type($file));
        header('Content-Length: '.filesize($file));
        readfile($file);
        die();
    }

    public function dump():void
    {
        echo '<pre>';
        echo '<h1>Suite Data</h1>';
        print_r($this->suiteData);
        echo '<h1>Suite Data Part</h1>';
        print_r($this->suiteDataPart);
        echo '<h1>Content Data</h1>';
        print_r($this->contentData);
        echo '</pre>';
    }

    public function dd():void
    {
        $this->dump();
        die();
    }

}
