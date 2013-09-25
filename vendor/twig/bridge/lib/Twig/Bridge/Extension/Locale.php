<?php

class Twig_Bridge_Extension_Locale extends Twig_Extension
{

    protected $Locale;

    public function __construct(\moss\component\locale\LocaleInterface $Locale = null)
    {
        $this->Locale = & $Locale;
    }

    public function getTokenParsers()
    {
        return array(new Twig_Bridge_TokenParser_Trans());
    }

    public function getFilters()
    {
        return array(
            'trans' => new Twig_Filter_Method($this, 'trans'),
            'translate' => new \Twig_Filter_Method($this, 'trans'),
            'translateInserted' => new \Twig_Filter_Method($this, 'transInserted', array('is_safe' => array('html')))
        );
    }

    public function getName()
    {
        return 'Locale';
    }

    public function trans($string)
    {
        if (!$this->Locale) {
            return $string;
        }

        return $this->Locale->get($string);
    }

    public function ntrans($string, $plural, $count)
    {
        if (!$this->Locale) {
            return $string;
        }

        return $this->Locale->get($string, $plural, $count);
    }

    public function transInserted($string)
    {
        if (!$this->Locale) {
            return $string;
        }

        return $this->Locale->insert($string);
    }
}