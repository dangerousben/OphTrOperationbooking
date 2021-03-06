<?php /**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */
 ?>
<section class="element <?php echo $element->elementType->class_name?>"
	data-element-type-id="<?php echo $element->elementType->id?>"
	data-element-type-class="<?php echo $element->elementType->class_name?>"
	data-element-type-name="<?php echo $element->elementType->name?>"
	data-element-display-order="<?php echo $element->elementType->display_order?>">
	<header class="element-header">
		<h3 class="element-title"><?php  echo $element->elementType->name; ?></h3>
	</header>
	<fieldset class="element-fields">
	<?php echo $form->radioButtons($element, 'eye_id', 'eye')?>
	<?php $form->widget('application.widgets.ProcedureSelection',array(
		'element' => $element,
		'durations' => true,
	))?>
	<?php echo $form->radioBoolean($element, 'consultant_required')?>
	<?php echo $form->radioButtons($element, 'anaesthetic_type_id', 'anaesthetic_type')?>
	<?php echo $form->radioBoolean($element, 'overnight_stay')?>
	<?php
	$criteria = new CDbCriteria;
	$criteria->compare('institution_id',1);
	$criteria->order = 'short_name asc';
	?>
	<?php echo $form->dropDownList($element, 'site_id', CHtml::listData(Site::model()->findAll($criteria),'id','short_name'),array(),false,array('field'=>2))?>
	<?php echo $form->radioButtons($element, 'priority_id', 'ophtroperationbooking_operation_priority')?>
	<?php echo $form->datePicker($element, 'decision_date', array('maxDate' => 'today'), array(), array_merge($form->layoutColumns, array('field' => 2)))?>
	<?php echo $form->textArea($element, 'comments', array('rows' => 4), false, array(), array_merge($form->layoutColumns, array('field' => 4)))?>
	<?php echo $form->textArea($element, 'comments_rtt', array('rows' => 4), false, array(), array_merge($form->layoutColumns, array('field' => 4)))?>
	</fieldset>
</section>
