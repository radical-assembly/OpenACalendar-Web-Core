<?php

namespace sysadmin\forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ActionWithCommentForm extends AbstractType{

	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('action', 'text', array('label'=>'Action','required'=>false, 'attr' => array('autocomplete' => 'off')));

		$builder->add('comment', 'textarea', array(
			'label'=>'Comment',
			'required'=>false,
		));
	}
	
	public function getName() {
		return 'ActionWithCommentForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}


