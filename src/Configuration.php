<?php

declare(strict_types=1);

namespace JBSNewMedia\MarkdownSuite;

class Configuration
{

    protected string $project_dir = '';

    protected string $project_url = '';

    protected string $project_lang = '';

    protected string $project_title = '';

    protected string $project_description = '';

    protected string $project_icon = '';

    protected string $project_icon_title = '';

    protected string $project_subtitle = '';

    protected string $project_main_title_start = '';

    protected string $project_main_title_end = '';

    protected string $project_footer_start = '';

    protected string $project_footer_end = '';

	protected array $project_allowed_file_extensions = [];

    public function __construct(protected array $configuration = [])
    {
        foreach ($configuration as $key => $value) {
            $method = 'set'.str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    public function setProjectDir(string $project_dir):self
    {
        $this->project_dir = $project_dir;

        return $this;
    }

    public function getProjectDir():string
    {
        return $this->project_dir;
    }

    public function setProjectUrl(string $project_url):self
    {
        $this->project_url = $project_url;

        return $this;
    }

    public function getProjectUrl():string
    {
        return $this->project_url;
    }

    public function setProjectLang(string $project_lang):self
    {
        $this->project_lang = $project_lang;

        return $this;
    }

    public function getProjectLang():string
    {
        return $this->project_lang;
    }

    public function setProjectTitle(string $project_title):self
    {
        $this->project_title = $project_title;

        return $this;
    }

    public function getProjectTitle():string
    {
        return $this->project_title;
    }

    public function setProjectDescription(string $project_description):self
    {
        $this->project_description = $project_description;

        return $this;
    }

    public function getProjectDescription():string
    {
        return $this->project_description;
    }

    public function setProjectIcon(string $project_icon):self
    {
        $this->project_icon = $project_icon;

        return $this;
    }

    public function getProjectIcon():string
    {
        return $this->project_icon;
    }

    public function setProjectIconTitle(string $project_icon_title):self
    {
        $this->project_icon_title = $project_icon_title;

        return $this;
    }

    public function getProjectIconTitle():string
    {
        return $this->project_icon_title;
    }

    public function setProjectSubtitle(string $project_subtitle):self
    {
        $this->project_subtitle = $project_subtitle;

        return $this;
    }

    public function getProjectSubtitle():string
    {
        return $this->project_subtitle;
    }

    public function setProjectMainTitleStart(string $project_main_title_start):self
    {
        $this->project_main_title_start = $project_main_title_start;

        return $this;
    }

    public function getProjectMainTitleStart():string
    {
        return $this->project_main_title_start;
    }

    public function setProjectMainTitleEnd(string $project_main_title_end):self
    {
        $this->project_main_title_end = $project_main_title_end;

        return $this;
    }

    public function getProjectMainTitleEnd():string
    {
        return $this->project_main_title_end;
    }

    public function setProjectFooterStart(string $project_footer_start):self
    {
        $this->project_footer_start = $project_footer_start;

        return $this;
    }

    public function getProjectFooterStart():string
    {
        return $this->project_footer_start;
    }

    public function setProjectFooterEnd(string $project_footer_end):self
    {
        $this->project_footer_end = $project_footer_end;

        return $this;
    }

    public function getProjectFooterEnd():string
    {
        return $this->project_footer_end;
    }

	public function setProjectAllowedFileExtensions(array $project_allowed_file_extensions):self
	{
		$this->project_allowed_file_extensions = $project_allowed_file_extensions;

		return $this;
	}

	public function getProjectAllowedFileExtensions():array
	{
		return $this->project_allowed_file_extensions;
	}

}
