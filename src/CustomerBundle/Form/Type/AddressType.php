<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\CustomerBundle\Form\Type;

use Sonata\Component\Basket\BasketInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Vincent Composieux <composieux@ekino.com>
 *
 * Address form type (used for customer addresses add/edit actions)
 */
class AddressType extends AbstractType
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $getter;

    /**
     * @var string
     */
    protected $name;

    /**
     * Constructor.
     *
     * @param string          $class  A class to apply getter
     * @param string          $getter A getter method name
     * @param string          $name   A form type name
     * @param BasketInterface $basket Sonata e-commerce basket instance
     */
    public function __construct($class, $getter, $name, BasketInterface $basket)
    {
        $this->class = $class;
        $this->getter = $getter;
        $this->name = $name;
        $this->basket = $basket;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $address = $builder->getData();

        $countryOptions = array();
        $countries = array();

        if ('delivery' == $options['context'] && $address) {
            $countries = $this->getBasketDeliveryCountries();
        }

        if (count($countries) > 0) {
            $countryOptions['choices'] = $countries;
        }

        $builder->add('countryCode', 'country', $countryOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'sonata_basket_address';
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'context' => 'default',
            'types' => call_user_func(array($this->class, $this->getter)),
        ));
    }

    /**
     * Returns basket elements delivery countries.
     *
     * @return array
     */
    protected function getBasketDeliveryCountries()
    {
        $countries = array();

        foreach ($this->basket->getBasketElements() as $basketElement) {
            $product = $basketElement->getProduct();

            foreach ($product->getDeliveries() as $delivery) {
                $code = $delivery->getCountryCode();

                if (!isset($countries[$code])) {
                    $countries[$code] = Intl::getRegionBundle()->getCountryName($code);
                }
            }
        }

        return $countries;
    }
}
