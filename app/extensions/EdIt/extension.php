<?php
namespace EdIt;

use Symfony\Component\HttpFoundation\Response, Symfony\Component\Translation\Loader as TranslationLoader;
use Symfony\Component\Yaml\Dumper as YamlDumper, Symfony\Component\Yaml\Parser as YamlParser, Symfony\Component\Yaml\Exception\ParseException;

class EdItException extends \Exception
{
}
;

class Extension extends \Bolt\BaseExtension
{

    protected $authorized = false;

    protected $config;

    /**
     *
     * @return array
     */
    public function info()
    {
        return array(
            'name' => "EdIt",
            'description' => "Edit content where it is",
            'tags' => array(
                'content',
                'editor',
                'admin',
                'tool'
            ),
            'type' => "Administrative Tool",
            'author' => "Rix Beck / Neologik Team",
            'link' => "http://www.neologik.hu",
            'email' => 'rix@neologik.hu',
            'version' => "0.1",

            'required_bolt_version' => "1.5.2",
            'highest_bolt_version' => "1.5.2",
            'first_releasedate' => "2014-03-31",
            'latest_releasedate' => "2014-03-31"
        );
    }

    /**
     * Initialize extension
     */
    public function initialize()
    {
        $this->config = $this->getConfig();

        if (! isset($this->config['permissions']) || ! is_array($this->config['permissions'])) {
            $this->config['permissions'] = array(
                'root',
                'admin',
                'developer'
            );
        } else {
            $this->config['permissions'][] = 'root';
        }

        $currentUser = $this->app['users']->getCurrentUser();
        $currentUserId = $currentUser['id'];

        foreach ($this->config['permissions'] as $role) {
            if ($this->app['users']->hasRole($currentUserId, $role)) {
                $this->authorized = true;
                break;
            }
        }

        if ($this->authorized) {

            $editorjs = $this->config['editorjs'];
            $editorcss = $this->config['editorcss'];
            $startup = $this->config['startup'];

            $this->addJavascript("assets/{$editorjs}");
            $this->addJquery();
            $this->addCSS("assets/{$editorcss}");
            $this->addJavascript("assets/{$startup}", true);

            $this->addTwigFunction('editable', 'twigEditable');

            $this->app->get("/edit/saveit/", array(
                $this,
                'saveit'
            ))->bind('saveit');
        }
    }

    public function saveit()
    {}

    /**
     * Twig function {{ editable('foo') }} in In Place Editor extension.
     */
    function twigEditable($record, $fieldname)
    {
        $slug = $record->contenttype['slug'];
        $id = $record->id;

        $html = "<editable data-content_id=\"ext-edit-{$slug}-{$id}\">" . $record->values[$fieldname] . "</editable>";

        return new \Twig_Markup($html, 'UTF-8');
    }
}