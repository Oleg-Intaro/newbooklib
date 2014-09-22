<?php

namespace Intaro\BookBundle\Twig;

/**
 * Выводит картинки с заданным размером
 */
class ImageResizerExtention extends \Twig_Extension
{
    /**
     * @return \Twig_SimpleFilter
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('img', array($this, 'resizeImg'), array(
                'is_safe' => array('html'),
            )),
        );
    }

    /**
     * Возвращает html img тег с заданной шириной и высотой
     * 
     * @param type $image
     * @param type $width
     * @param type $height
     * 
     * @return string 
     */
    public function resizeImg($image, $width = 100, $height = 100)
    {
        return '<img src="'.$image.'" width="'.$width.'" height="'.$height.'" />';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'intaro_image_resizer_extension';
    }
}
