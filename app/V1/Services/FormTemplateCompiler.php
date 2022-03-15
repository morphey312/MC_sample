<?php

namespace App\V1\Services;

use App\V1\Contracts\Services\FormTemplateCompiler as FormTemplateCompilerInterface;
use SimpleXMLElement;
use Exception;

class FormTemplateCompiler implements FormTemplateCompilerInterface
{
    /**
     * @inheritdoc
     */
    public function compile($xml)
    {
        $structure = [];
        $fields = [];
        $doc = new SimpleXMLElement($xml);
        
        foreach ($doc->children() as $section) {
            if ($this->nodeName($section) === 'section') {
                $structure[] = $this->parseSection($section, $fields);
            } else {
                throw new Exception(sprintf('Unexpected tag "%s" when "section" is expected', $this->nodeName($section)));
            }
        }
        
        return [
            'structure' => $structure,
            'fields' => $fields,
        ];
    }
    
    /**
     * Parse section
     * 
     * @param SimpleXMLElement $section
     * @param array $fields
     * 
     * @return array
     */ 
    protected function parseSection($section, &$fields)
    {
        $result = [
            'label' => (string) $section['label'],
            'children' => $this->parseChildren($section, $fields),
        ];
        
        $hint = (string) $section['hint'];
        if ($hint) {
            $result['hint'] = $hint;
        }

        $sectionKey = (string) $section['sectionKey'];
        if ($sectionKey) {
            $result['sectionKey'] = $sectionKey;
        }
        
        return $result;
    }
    
    /**
     * Parse line
     * 
     * @param SimpleXMLElement $section
     * @param array $fields
     * 
     * @return array
     */ 
    protected function parseLine($section, &$fields)
    {
        return $this->parseChildren($section, $fields);
    }

    /**
     * Parse line
     *
     * @param SimpleXMLElement $section
     * @param array $fields
     *
     * @return array
     */
    protected function parseSketchpad($sketchpad, &$fields)
    {
        $name = strtolower($sketchpad['name']);

        $fieldData = [
            'type' => 'sketchpad',
            'name' => $name,
            'hint' => $sketchpad['hint'] || $sketchpad['label']
        ];

        $fields[$name] = $fieldData;

        return [
            'html' => $sketchpad->asXML(),
            'label' => (string) $sketchpad['label'],
            'height' => (string) $sketchpad['height'],
            'width' => (string) $sketchpad['width'],
            'isSketchpad' => true
        ];
    }

    /**
     * Parse section children
     * 
     * @param SimpleXMLElement $section
     * @param array $fields
     * 
     * @return array
     */ 
    protected function parseChildren($section, &$fields)
    {
        $children = [];
        foreach ($section->children() as $node) {
            $type = $this->nodeName($node);
            if ($type === 'section') {
                $children[] = $this->parseSection($node, $fields);
            } elseif ($type === 'line') {
                $children[] = $this->parseLine($node, $fields);
            } elseif ($type === 'sketchpad') {
                $children[] = $this->parseSketchpad($node, $fields);
            }
            else {
                $name = strtolower($node['name']);
                if (!$name) {
                    throw new Exception(sprintf('Field name is missing on: %s', $node->asXML()));
                }
                if (array_key_exists($name, $fields)) {
                    throw new Exception(sprintf('Duplicated field "%s"', $name));
                }
                try {
                    $options = $this->parseOptions($node);
                } catch (Exception $e) {
                    throw new Exception(sprintf('On "%s": %s', $name, $e->getMessage()));
                }
                $fieldData = [
                    'type' => $type,
                    'name' => $name,
                    'label' => (string) $node['label'],
                ];
                if (is_array($options)) {
                    $fieldData['options'] = $options;
                    if ($this->boolAttr($node, 'multiple', true)) {
                        $fieldData['multiple'] = true;
                    }
                } elseif ($this->boolAttr($node, 'boolean')) {
                    $fieldData['options'] = [
                        ['key' => 1, 'label' => 'да'],
                        ['key' => 2, 'label' => 'нет'],
                    ];
                }
                $hint = (string) $node['hint'];
                if ($hint) {
                    $fieldData['hint'] = $hint;
                }
                if ($this->boolAttr($node, 'multiline')) {
                    $fieldData['multiline'] = true;
                }
                $suffix = (string) $node['suffix'];
                if ($suffix) {
                    $fieldData['suffix'] = $suffix;
                }
                $width = (string) $node['width'];
                if ($width) {
                    $fieldData['width'] = $width;
                }
                $className = (string) $node['className'];
                if ($className) {
                    $fieldData['className'] = $className;
                }
                
                $fields[$name] = $fieldData;
                $children[] = &$fields[$name];
            }
        }
        return $children;
    }
    
    /**
     * Get node options
     * 
     * @param SimpleXMLElement $node
     * 
     * @return mixed
     */ 
    protected function parseOptions($node)
    {
        $options = [];
        foreach ($node->children() as $option) {
            if ($this->nodeName($option) === 'option') {
                $key = strtolower($option['key']);
                if (!is_numeric($key)) {
                    throw new Exception(sprintf('Option key must be numeric, but "%s" given', $key));
                }
                if (array_key_exists($key, $options)) {
                    throw new Exception(sprintf('Duplicated option key "%s"', $key));
                }
                $optionData = [
                    'key' => (int) $key,
                    'label' => (string) $option,
                ];
                $hint = (string) $option['hint'];
                if ($hint) {
                    $optionData['hint'] = $hint;
                }
                $options[$key] = $optionData;
            } else {
                throw new Exception(sprintf('Unexpected tag "%s" when "option" is expected', $this->nodeName($option)));
            }
        }
        return count($options) !== 0 ? array_values($options) : false;
    }
    
    /**
     * Get node name
     * 
     * @param SimpleXMLElement $node
     * 
     * @return string
     */ 
    protected function nodeName($node) 
    {
        return strtolower($node->getName());
    }
    
    /**
     * Get bool attribute value
     * 
     * @param SimpleXMLElement $node
     * @param string $attr
     * @param bool $default
     * 
     * @return bool
     */ 
    protected function boolAttr($node, $attr, $default = false) 
    {
        $val = strtolower($node[$attr]);
        if ($val === 'true') {
            return true;
        }
        if ($val === 'false') {
            return false;
        }
        return $default;
    }
}
