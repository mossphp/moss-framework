<?php

interface Twig_Bridge_Extension_TransInterface
{

    /**
     * Returns localized message
     *
     * @param string $message
     * @param array  $arguments
     * @param string $locale
     *
     * @return string
     */
    public function trans($message, $arguments, $locale);

    /**
     * Returns plural localized message
     * Input message eg.:
     * {0} There are no apples|{1} There is one apple|]1,19] There are %count% apples|[20,Inf] There are many apples
     *
     * @param string $message
     * @param int    $count
     * @param array  $arguments
     * @param string $locale
     *
     * @return string
     */
    public function transChoice($message, $count, $arguments, $locale);
}