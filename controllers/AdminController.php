<?php
/**
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

class AdminController extends ModuleAdminController
{
	public $sequences_items_per_page = 20;
	public $sessions_items_per_page = 20;

	public function actionViewERODRules()
	{
		Audit::add('admin','list',null,false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_EROD_Rule'));

		$this->render('erodrules');
	}

	public function actionEditERODRule($id)
	{
		if (!$erod = OphTrOperationbooking_Operation_EROD_Rule::model()->findByPk($id)) {
			throw new Exception("EROD rule not found: $id");
		}

		$errors = array();

		if (!empty($_POST)) {
			$erod->subspecialty_id = $_POST['OphTrOperationbooking_Operation_EROD_Rule']['subspecialty_id'];
			if (!$erod->save()) {
				$errors = $erod->getErrors();
			} else {
				$firm_ids = array();
				foreach ($erod->items as $item) {
					$firm_ids[] = $item['item_id'];
				}

				foreach ($_POST['Firms'] as $firm_id) {
					if (!in_array($firm_id,$firm_ids)) {
						$item = new OphTrOperationbooking_Operation_EROD_Rule_Item;
						$item->erod_rule_id = $erod->id;
						$item->item_type = 'firm';
						$item->item_id = $firm_id;
						if (!$item->save()) {
							$errors = array_merge($errors,$item->getErrors());
						}
					}
				}

				foreach ($firm_ids as $firm_id) {
					if (!in_array($firm_id,$_POST['Firms'])) {
						if (!$item = OphTrOperationbooking_Operation_EROD_Rule_Item::model()->find('erod_rule_id=? and item_type=? and item_id=?',array($erod->id,'firm',$firm_id))) {
							throw new Exception("Rule item not found: [$erod->id][firm][$firm_id]");
						}
						if (!$item->delete()) {
							throw new Exception("Rule item delete failed: ".print_r($item->getErrors(),true));
						}
					}
				}

				if (empty($errors)) {
					Audit::add('admin','update',serialize(array_merge(array('id'=>$id),$_POST)),false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_EROD_Rule'));
					$this->redirect(array('/OphTrOperationbooking/admin/viewERODRules'));
				}
			}
		}

		Audit::add('admin','view',$id,false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_EROD_Rule'));

		$this->render('/admin/editerodrule',array(
			'erod' => $erod,
			'errors' => $errors,
		));
	}

	public function actionAddERODRule()
	{
		$errors = array();

		$erod = new OphTrOperationbooking_Operation_EROD_Rule;

		if (!empty($_POST)) {
			$erod->subspecialty_id = $_POST['OphTrOperationbooking_Operation_EROD_Rule']['subspecialty_id'];
			if (!$erod->save()) {
				$errors = $erod->getErrors();
			} else {
				$firm_ids = array();
				foreach ($erod->items as $item) {
					$firm_ids[] = $item['item_id'];
				}

				foreach ($_POST['Firms'] as $firm_id) {
					if (!in_array($firm_id,$firm_ids)) {
						$item = new OphTrOperationbooking_Operation_EROD_Rule_Item;
						$item->erod_rule_id = $erod->id;
						$item->item_type = 'firm';
						$item->item_id = $firm_id;
						if (!$item->save()) {
							$errors = array_merge($errors,$item->getErrors());
						}
					}
				}

				foreach ($firm_ids as $firm_id) {
					if (!in_array($firm_id,$_POST['Firms'])) {
						if (!$item = OphTrOperationbooking_Operation_EROD_Rule_Item::model()->find('erod_rule_id=? and item_type=? and item_id=?',array($erod->id,'firm',$firm_id))) {
							throw new Exception("Rule item not found: [$erod->id][firm][$firm_id]");
						}
						if (!$item->delete()) {
							throw new Exception("Rule item delete failed: ".print_r($item->getErrors(),true));
						}
					}
				}

				if (empty($errors)) {
					Audit::add('admin','create',serialize($_POST),false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_EROD_Rule'));
					$this->redirect(array('/OphTrOperationbooking/admin/viewERODRules'));
				}
			}
		}

		$this->render('/admin/editerodrule',array(
			'erod' => $erod,
			'errors' => $errors,
		));
	}

	public function actionDeleteERODRules()
	{
		if (!empty($_POST['erod'])) {
			foreach ($_POST['erod'] as $erod_id) {
				if ($_erod = OphTrOperationbooking_Operation_EROD_Rule::model()->findByPk($erod_id)) {
					foreach ($_erod->items as $item) {
						if (!$item->delete()) {
							throw new Exception("Unable to delete rule item: ".print_r($item->getErrors(),true));
						}
					}
					if (!$_erod->delete()) {
						throw new Exception("Unable to delete erod rule: ".print_r($_erod->getErrors(),true));
					}
				}
			}

			Audit::add('admin','delete',serialize($_POST),false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_EROD_Rule'));
		}

		echo "1";
	}

	public function actionViewLetterContactRules()
	{
		$this->jsVars['OE_rule_model'] = 'LetterContactRule';

		Audit::add('admin','list',null,false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Letter_Contact_Rule'));

		$this->render('lettercontactrules',array(
			'data' => OphTrOperationbooking_Letter_Contact_Rule::model()->findAllAsTree(),
		));
	}

	public function actionTestLetterContactRules()
	{
		$site_id = @$_POST['lcr_site_id'];
		$subspecialty_id = @$_POST['lcr_subspecialty_id'];
		$theatre_id = @$_POST['lcr_theatre_id'];
		$firm_id = @$_POST['lcr_firm_id'];

		$criteria = new CDbCriteria;
		$criteria->addCondition('parent_rule_id is null');
		$criteria->order = 'rule_order asc';

		$rule_ids = array();

		foreach (OphTrOperationbooking_Letter_Contact_Rule::model()->findAll($criteria) as $rule) {
			if ($rule->applies($site_id,$subspecialty_id,$theatre_id,$firm_id)) {
				$final = $rule->parse($site_id,$subspecialty_id,$theatre_id,$firm_id);
				echo json_encode(array($final->id));
				return;
			}
		}

		echo json_encode(array());
	}

	public function actionEditLetterContactRule($id)
	{
		if (!$rule = OphTrOperationbooking_Letter_Contact_Rule::model()->findByPk($id)) {
			throw new Exception("Letter contact rule not found: $id");
		}

		$errors = array();

		if (!empty($_POST)) {
			$rule->attributes = $_POST['OphTrOperationbooking_Letter_Contact_Rule'];

			if (!$rule->save()) {
				$errors = $rule->getErrors();
			} else {
				Audit::add('admin','update',serialize(array_merge(array('id'=>$id),$_POST)),false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Letter_Contact_Rule'));
				$this->redirect(array('/OphTrOperationbooking/admin/viewLetterContactRules'));
			}
		}

		$this->jsVars['OE_rule_model'] = 'LetterContactRule';

		Audit::add('admin','view',$id,false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Letter_Contact_Rule'));

		$this->render('editlettercontactrule',array(
			'rule' => $rule,
			'errors' => $errors,
		));
	}

	public function actionDeleteLetterContactRule($id)
	{
		if (!$rule = OphTrOperationbooking_Letter_Contact_Rule::model()->findByPk($id)) {
			throw new Exception("Letter contact rule not found: $id");
		}

		$errors = array();

		if (!empty($_POST)) {
			if (@$_POST['delete']) {
				if (!$rule->delete()) {
					$errors = $rule->getErrors();
				} else {
					Audit::add('admin','delete',$id,false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Letter_Contact_Rule'));
					$this->redirect(array('/OphTrOperationbooking/admin/viewLetterContactRules'));
				}
			}
		}

		$this->jsVars['OE_rule_model'] = 'LetterContactRule';

		$this->render('deletelettercontactrule',array(
			'rule' => $rule,
			'errors' => $errors,
		));
	}

	public function actionAddLetterContactRule()
	{
		$rule = new OphTrOperationbooking_Letter_Contact_Rule;

		$errors = array();

		if (!empty($_POST)) {
			$rule->attributes = $_POST['OphTrOperationbooking_Letter_Contact_Rule'];

			if (!$rule->save()) {
				$errors = $rule->getErrors();
			} else {
				Audit::add('admin','create',serialize($_POST),false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Letter_Contact_Rule'));
				$this->redirect(array('/OphTrOperationbooking/admin/viewLetterContactRules'));
			}
		} else {
			if (isset($_GET['parent_rule_id'])) {
				$rule->parent_rule_id = $_GET['parent_rule_id'];
			}
		}

		$this->jsVars['OE_rule_model'] = 'LetterContactRule';

		$this->render('editlettercontactrule',array(
			'rule' => $rule,
			'errors' => $errors,
		));
	}

	public function actionViewLetterWarningRules()
	{
		$this->jsVars['OE_rule_model'] = 'LetterWarningRule';

		Audit::add('admin','list',null,false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Admission_Letter_Warning_Rule'));

		$this->render('letterwarningrules',array(
			'data' => OphTrOperationbooking_Admission_Letter_Warning_Rule::model()->findAllAsTree(),
		));
	}

	public function actionTestLetterWarningRules()
	{
		$site_id = @$_POST['lcr_site_id'];
		$subspecialty_id = @$_POST['lcr_subspecialty_id'];
		$theatre_id = @$_POST['lcr_theatre_id'];
		$firm_id = @$_POST['lcr_firm_id'];
		$is_child = @$_POST['lcr_is_child'];

		$criteria = new CDbCriteria;
		$criteria->addCondition('parent_rule_id is null');
		$criteria->addCondition('rule_type_id = :rule_type_id');
		$criteria->params[':rule_type_id'] = @$_POST['lcr_rule_type_id'];
		$criteria->order = 'rule_order asc';

		$rule_ids = array();

		foreach (OphTrOperationbooking_Admission_Letter_Warning_Rule::model()->findAll($criteria) as $rule) {
			if ($rule->applies($site_id, $is_child, $theatre_id, $subspecialty_id, $firm_id)) {
				$final = $rule->parse($site_id, $is_child, $theatre_id, $subspecialty_id, $firm_id);
				echo json_encode(array($final->id));
				return;
			}
		}

		echo json_encode(array());
	}

	public function actionEditLetterWarningRule($id)
	{
		if (!$rule = OphTrOperationbooking_Admission_Letter_Warning_Rule::model()->findByPk($id)) {
			throw new Exception("Letter warning rule not found: $id");
		}

		$errors = array();

		if (!empty($_POST)) {
			$rule->attributes = $_POST['OphTrOperationbooking_Admission_Letter_Warning_Rule'];

			if (!$rule->save()) {
				$errors = $rule->getErrors();
			} else {
				Audit::add('admin','update',$id,false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Admission_Letter_Warning_Rule'));
				$this->redirect(array('/OphTrOperationbooking/admin/viewLetterWarningRules'));
			}
		}

		Audit::add('admin','view',$id,false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Admission_Letter_Warning_Rule'));

		$this->jsVars['OE_rule_model'] = 'LetterWarningRule';

		$this->render('editletterwarningrule',array(
			'rule' => $rule,
			'errors' => $errors,
		));
	}

	public function actionAddLetterWarningRule()
	{
		$rule = new OphTrOperationbooking_Admission_Letter_Warning_Rule;

		$errors = array();

		if (!empty($_POST)) {
			$rule->attributes = $_POST['OphTrOperationbooking_Admission_Letter_Warning_Rule'];

			if (!$rule->save()) {
				$errors = $rule->getErrors();
			} else {
				Audit::add('admin','create',serialize($_POST),false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Admission_Letter_Warning_Rule'));
				$this->redirect(array('/OphTrOperationbooking/admin/viewLetterWarningRules'));
			}
		} else {
			if (isset($_GET['parent_rule_id'])) {
				$rule->parent_rule_id = $_GET['parent_rule_id'];
			}
		}

		$this->jsVars['OE_rule_model'] = 'LetterWarningRule';

		$this->render('editletterwarningrule',array(
			'rule' => $rule,
			'errors' => $errors,
		));
	}

	public function actionDeleteLetterWarningRule($id)
	{
		if (!$rule = OphTrOperationbooking_Admission_Letter_Warning_Rule::model()->findByPk($id)) {
			throw new Exception("Letter warning rule not found: $id");
		}

		$errors = array();

		if (!empty($_POST)) {
			if (@$_POST['delete']) {
				if (!$rule->delete()) {
					$errors = $rule->getErrors();
				} else {
					Audit::add('admin','delete',$id,false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Admission_Letter_Warning_Rule'));
					$this->redirect(array('/OphTrOperationbooking/admin/viewLetterWarningRules'));
				}
			}
		}

		$this->jsVars['OE_rule_model'] = 'LetterWarningRule';

		$this->render('deleteletterwarningrule',array(
			'rule' => $rule,
			'errors' => $errors,
		));
	}

	public function actionViewWaitingListContactRules()
	{
		$this->jsVars['OE_rule_model'] = 'WaitingListContactRule';

		Audit::add('admin','list',null,false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Waiting_List_Contact_Rule'));

		$this->render('waitinglistcontactrules',array(
			'data' => OphTrOperationbooking_Waiting_List_Contact_Rule::model()->findAllAsTree(),
		));
	}

	public function actionTestWaitingListContactRules()
	{
		$site_id = @$_POST['lcr_site_id'];
		$service_id = @$_POST['lcr_service_id'];
		$firm_id = @$_POST['lcr_firm_id'];
		$is_child = @$_POST['lcr_is_child'];

		$criteria = new CDbCriteria;
		$criteria->addCondition('parent_rule_id is null');
		$criteria->order = 'rule_order asc';

		$rule_ids = array();

		foreach (OphTrOperationbooking_Waiting_List_Contact_Rule::model()->findAll($criteria) as $rule) {
			if ($rule->applies($site_id, $service_id, $firm_id, $is_child)) {
				$final = $rule->parse($site_id, $service_id, $firm_id, $is_child);
				echo json_encode(array($final->id));
				return;
			}
		}

		echo json_encode(array());
	}

	public function actionEditWaitingListContactRule($id)
	{
		if (!$rule = OphTrOperationbooking_Waiting_List_Contact_Rule::model()->findByPk($id)) {
			throw new Exception("Waiting list contact rule not found: $id");
		}

		$errors = array();

		if (!empty($_POST)) {
			$rule->attributes = $_POST['OphTrOperationbooking_Waiting_List_Contact_Rule'];

			if (!$rule->save()) {
				$errors = $rule->getErrors();
			} else {
				Audit::add('admin','update',serialize(array_merge(array('id'=>$id),$_POST)),false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Waiting_List_Contact_Rule'));
				$this->redirect(array('/OphTrOperationbooking/admin/viewWaitingListContactRules'));
			}
		}

		$this->jsVars['OE_rule_model'] = 'WaitingListContactRule';

		Audit::add('admin','list',null,false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Waiting_List_Contact_Rule'));

		$this->render('editwaitinglistcontactrule',array(
			'rule' => $rule,
			'errors' => $errors,
		));
	}

	public function actionDeleteWaitingListContactRule($id)
	{
		if (!$rule = OphTrOperationbooking_Waiting_List_Contact_Rule::model()->findByPk($id)) {
			throw new Exception("Waiting list contact rule not found: $id");
		}

		$errors = array();

		if (!empty($_POST)) {
			if (@$_POST['delete']) {
				if (!$rule->delete()) {
					$errors = $rule->getErrors();
				} else {
					Audit::add('admin','delete',serialize($_POST),false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Waiting_List_Contact_Rule'));
					$this->redirect(array('/OphTrOperationbooking/admin/viewWaitingListContactRules'));
				}
			}
		}

		$this->jsVars['OE_rule_model'] = 'WaitingListContactRule';

		$this->render('deletewaitinglistcontactrule',array(
			'rule' => $rule,
			'errors' => $errors,
			'data' => OphTrOperationbooking_Waiting_List_Contact_Rule::model()->findAllAsTree($rule,true,'textPlain'),
		));
	}

	public function actionViewOperationNameRules()
	{
		Audit::add('admin','list',null,false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Name_Rule'));

		$this->render('operationnamerules');
	}

	public function actionAddOperationNameRule()
	{
		$errors = array();

		$rule = new OphTrOperationbooking_Operation_Name_Rule;

		if (!empty($_POST)) {
			$rule->attributes = $_POST['OphTrOperationbooking_Operation_Name_Rule'];

			if (!$rule->save()) {
				$errors = $erod->getErrors();
			} else {
				Audit::add('admin','create',serialize($_POST),false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Name_Rule'));
				$this->redirect(array('/OphTrOperationbooking/admin/viewOperationNameRules'));
			}
		}

		$this->render('/admin/editoperationnamerule',array(
			'rule' => $rule,
			'errors' => $errors,
		));
	}

	public function actionEditOperationNameRule($id)
	{
		if (!$rule = OphTrOperationbooking_Operation_Name_Rule::model()->findByPk($id)) {
			throw new Exception("Operation name rule not found: $id");
		}

		$errors = array();

		if (!empty($_POST)) {
			$rule->attributes = $_POST['OphTrOperationbooking_Operation_Name_Rule'];

			if (!$rule->save()) {
				$errors = $rule->getErrors();
			} else {
				Audit::add('admin','update',serialize(array_merge(array('id'=>$id),$_POST)),false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Name_Rule'));
				$this->redirect(array('/OphTrOperationbooking/admin/viewOperationNameRules'));
			}
		}

		Audit::add('admin','view',$id,false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Name_Rule'));

		$this->render('/admin/editoperationnamerule',array(
			'rule' => $rule,
			'errors' => $errors,
		));
	}

	public function actionDeleteOperationNameRules()
	{
		if (!empty($_POST['operation_name'])) {
			foreach ($_POST['operation_name'] as $rule_id) {
				if ($_rule = OphTrOperationbooking_Operation_Name_Rule::model()->findByPk($rule_id)) {
					if (!$_rule->delete()) {
						throw new Exception("Unable to delete rule rule: ".print_r($_rule->getErrors(),true));
					}
				}
			}

			Audit::add('admin','delete',serialize($_POST),false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Name_Rule'));
		}

		echo "1";
	}

	public function actionViewSequences()
	{
		if (@$_GET['reset'] == 1) {
			unset($_GET['reset']);
			unset(Yii::app()->session['admin_sequences']);
			$this->redirectWith($_GET);
		}

		if (empty($_GET) && empty($_POST) && !empty(Yii::app()->session['admin_sequences'])) {
			$this->redirectWith(Yii::app()->session['admin_sequences']);
		} elseif (!empty($_GET)) {
			Yii::app()->session['admin_sequences'] = $_GET;
		}

		if (@$_POST['generateSessions']) {
			$api = Yii::app()->moduleAPI->get('OphTrOperationbooking');
			$api->generateSessions();
			Yii::app()->user->setFlash('success', "Sessions have been generated.");
			echo "1";
			return;
		}

		Audit::add('admin','list',null,false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Sequence'));

		$this->render('/admin/sequences',array(
			'sequences' => $this->getSequences(),
		));
	}

	public function redirectWith($params)
	{
		$uri = preg_replace('/\?.*$/','',$_SERVER['REQUEST_URI']);

		$first=true;
		foreach ($params as $key => $value) {
			$uri .= $first ? '?' : '&';
			$first=false;
			$uri .= "$key=$value";
		}

		$this->redirect(array($uri));
	}

	public function getSequences($all=false)
	{
		$criteria = new CDbCriteria;

		if ($firm = Firm::model()->findByPk(@$_REQUEST['firm_id'])) {
			$criteria->addCondition('firm_id=:firm_id');
			$criteria->params[':firm_id'] = $firm->id;
		} elseif (@$_REQUEST['firm_id'] == 'NULL') {
			$criteria->addCondition('firm_id is null');
		}

		if ($theatre = OphTrOperationbooking_Operation_Theatre::model()->findByPk(@$_REQUEST['theatre_id'])) {
			$criteria->addCondition('theatre_id=:theatre_id');
			$criteria->params[':theatre_id'] = $theatre->id;
		}

		if (@$_REQUEST['date_from'] && strtotime(@$_REQUEST['date_from'])) {
			$criteria->addCondition('start_date >= :start_date');
			$criteria->params[':start_date'] = date('Y-m-d',strtotime(@$_REQUEST['date_from']));
		}

		if (@$_REQUEST['date_to'] && strtotime(@$_REQUEST['date_to'])) {
			$criteria->addCondition('end_date <= :end_date');
			$criteria->params[':end_date'] = date('Y-m-d',strtotime(@$_REQUEST['date_to']));
		}

		if (@$_REQUEST['interval_id'] != '') {
			$criteria->addCondition('interval_id = :interval_id');
			$criteria->params[':interval_id'] = @$_REQUEST['interval_id'];
		}

		if (@$_REQUEST['weekday'] != '') {
			$criteria->addCondition('weekday = :weekday');
			$criteria->params[':weekday'] = @$_REQUEST['weekday'];
		}

		if (@$_REQUEST['consultant'] != '') {
			$criteria->addCondition('consultant = :consultant');
			$criteria->params[':consultant'] = @$_REQUEST['consultant'];
		}

		if (@$_REQUEST['paediatric'] != '') {
			$criteria->addCondition('paediatric = :paediatric');
			$criteria->params[':paediatric'] = @$_REQUEST['paediatric'];
		}

		if (@$_REQUEST['anaesthetist'] != '') {
			$criteria->addCondition('anaesthetist = :anaesthetist');
			$criteria->params[':anaesthetist'] = @$_REQUEST['anaesthetist'];
		}

		if (@$_REQUEST['general_anaesthetic'] != '') {
			$criteria->addCondition('general_anaesthetic = :general_anaesthetic');
			$criteria->params[':general_anaesthetic'] = @$_REQUEST['general_anaesthetic'];
		}

		$page = @$_REQUEST['page'] ? $_REQUEST['page'] : 1;

		if ($all) {
			return OphTrOperationbooking_Operation_Sequence::model()->findAll($criteria);
		}

		$count = OphTrOperationbooking_Operation_Sequence::model()->count($criteria);
		$pages = ceil($count/$this->sequences_items_per_page);

		if ($page <1) $page = 1;
		if ($page > $pages) $page = $pages;

		$criteria->limit = $this->sequences_items_per_page;
		$criteria->offset = ($page-1) * $this->sequences_items_per_page;

		$order = @$_REQUEST['order']=='desc' ? 'desc' : 'asc';

		switch (@$_REQUEST['sortby']) {
			case 'firm':
				$criteria->order = "firm.name $order, subspecialty.name $order";
				break;
			case 'theatre':
				$criteria->order = "theatre.name $order";
				break;
			case 'dates':
				$criteria->order = "start_date $order, end_date $order, start_time $order, end_time $order";
				break;
			case 'time':
				$criteria->order = "start_time $order, end_time $order";
				break;
			case 'interval':
				$criteria->order = "interval.name $order";
				break;
			case 'weekday':
				$criteria->order = "weekday $order";
				break;
			default:
				$criteria->order = "firm.name $order, subspecialty.name $order";
		}


		$with = array(
			'firm' => array(
				'with' => array(
					'serviceSubspecialtyAssignment' => array(
						'with' => 'subspecialty',
					),
				),
			),
			'theatre',
			'interval',
		);

		$this->items_per_page = $this->sessions_items_per_page;
		$pagination = $this->initPagination(OphTrOperationbooking_Operation_Sequence::model()->with($with), $criteria);
		$data = OphTrOperationbooking_Operation_Sequence::model()->with($with)->findAll($criteria);

		return array(
			'data' => $data,
			'pagination' => $pagination
		);
	}

	public function getUri($elements)
	{
		$uri = preg_replace('/\?.*$/','',$_SERVER['REQUEST_URI']);

		$request = $_REQUEST;

		if (isset($elements['sortby']) && $elements['sortby'] == @$request['sortby']) {
			$request['order'] = (@$request['order'] == 'desc') ? 'asc' : 'desc';
		} elseif (isset($request['sortby']) && isset($elements['sortby']) && $request['sortby'] != $elements['sortby']) {
			$request['order'] = 'asc';
		}

		$first = true;
		foreach (array_merge($request,$elements) as $key => $value) {
			$uri .= $first ? '?' : '&';
			$first = false;
			$uri .= "$key=$value";
		}

		return $uri;
	}

	public function actionSequenceInlineEdit()
	{
		$errors = array();

		if (!empty($_POST['sequence'])) {
			$criteria = new CDbCriteria;
			$criteria->addInCondition('id',$_POST['sequence']);
			$sequences = OphTrOperationbooking_Operation_Sequence::model()->findAll($criteria);
		} elseif (@$_POST['use_filters']) {
			$sequences = $this->getSequences(true);
		}

		foreach ($sequences as $sequence) {
			$changed = false;

			foreach (array('firm_id','theatre_id','start_time','end_time','interval_id','weekday','consultant','paediatric','anaesthetist','general_anaesthetic') as $field) {
				if ($_POST['inline_'.$field] != '') {
					if ($sequence->$field != $_POST['inline_'.$field]) {
						$sequence->$field = $_POST['inline_'.$field];
						$changed = true;
					}
				}
			}
			if ($_POST['inline_start_date'] != '') {
				if (!strtotime($_POST['inline_start_date'])) {
					$errors['start_date'] = "Invalid start date";
				}
				if ($sequence->start_date != date('Y-m-d',strtotime($_POST['inline_start_date']))) {
					$sequence->start_date = date('Y-m-d',strtotime($_POST['inline_start_date']));
					$changed = true;
				}
			}
			if ($_POST['inline_end_date'] != '') {
				if (!strtotime($_POST['inline_end_date'])) {
					$errors['end_date'] = "Invalid end date";
				}
				if ($sequence->end_date != date('Y-m-d',strtotime($_POST['inline_end_date']))) {
					$sequence->end_date = date('Y-m-d',strtotime($_POST['inline_end_date']));
					$changed = true;
				}
			}
			if ($_POST['inline_update_weeks']) {
				$weeks = 0;
				$_POST['inline_week1'] && $weeks += 1;
				$_POST['inline_week2'] && $weeks += 2;
				$_POST['inline_week3'] && $weeks += 4;
				$_POST['inline_week4'] && $weeks += 8;
				$_POST['inline_week5'] && $weeks += 16;

				if ($sequence->week_selection != $weeks) {
					$sequence->week_selection = $weeks;
					$changed = true;
				}
			}

			if ($changed) {
				if (!empty($errors)) {
					$sequence->validate();
					echo json_encode(array_merge($errors,$sequence->getErrors()));
					return;
				}

				if (!$sequence->save()) {
					echo json_encode($sequence->getErrors());
					return;
				}

				Audit::add('admin','update',serialize(array_merge(array('id'=>$sequence->id),$_POST)),false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Sequence'));
			}
		}

		echo json_encode($errors);
	}

	public function actionEditSequence($id)
	{
		if (!$sequence = OphTrOperationbooking_Operation_Sequence::model()->findByPk($id)) {
			throw new Exception("Sequence not found: $id");
		}

		$errors = array();

		// check for conflicts with other sessions

		if (!empty($_POST)) {
			$sequence->attributes = $_POST['OphTrOperationbooking_Operation_Sequence'];

			$weeks = 0;

			if ($_POST['OphTrOperationbooking_Operation_Sequence']['week_selection_week1']) $weeks += 1;
			if ($_POST['OphTrOperationbooking_Operation_Sequence']['week_selection_week2']) $weeks += 2;
			if ($_POST['OphTrOperationbooking_Operation_Sequence']['week_selection_week3']) $weeks += 4;
			if ($_POST['OphTrOperationbooking_Operation_Sequence']['week_selection_week4']) $weeks += 8;
			if ($_POST['OphTrOperationbooking_Operation_Sequence']['week_selection_week5']) $weeks += 16;

			$sequence->week_selection = $weeks;

			if (!$sequence->end_date) {
				$sequence->end_date = null;
			}
			if (!$sequence->week_selection) {
				$sequence->week_selection = null;
			}

			if (!$sequence->save()) {
				$errors = $sequence->getErrors();
			} else {
				if (empty($errors)) {
					Audit::add('admin','update',serialize(array_merge(array('id'=>$id),$_POST)),false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Sequence'));
					$this->redirect(array('/OphTrOperationbooking/admin/viewSequences'));
				}
			}
		}

		Audit::add('admin','view',$id,false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Sequence'));

		$this->render('/admin/editsequence',array(
			'sequence' => $sequence,
			'errors' => $errors,
		));
	}

	public function actionAddSequence()
	{
		$sequence = new OphTrOperationbooking_Operation_Sequence;

		$errors = array();

		if (!empty($_POST)) {
			$sequence->attributes = $_POST['OphTrOperationbooking_Operation_Sequence'];

			$weeks = 0;

			if ($_POST['OphTrOperationbooking_Operation_Sequence']['week_selection_week1']) $weeks += 1;
			if ($_POST['OphTrOperationbooking_Operation_Sequence']['week_selection_week2']) $weeks += 2;
			if ($_POST['OphTrOperationbooking_Operation_Sequence']['week_selection_week3']) $weeks += 4;
			if ($_POST['OphTrOperationbooking_Operation_Sequence']['week_selection_week4']) $weeks += 8;
			if ($_POST['OphTrOperationbooking_Operation_Sequence']['week_selection_week5']) $weeks += 16;

			$sequence->week_selection = $weeks;

			if (!$sequence->end_date) {
				$sequence->end_date = null;
			}
			if (!$sequence->week_selection) {
				$sequence->week_selection = null;
			}

			if (!$sequence->save()) {
				$errors = $sequence->getErrors();
			} else {
				if (empty($errors)) {
					Audit::add('admin','create',serialize($_POST),false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Sequence'));
					$this->redirect(array('/OphTrOperationbooking/admin/viewSequences'));
				}
			}
		}

		$this->render('/admin/editsequence',array(
			'sequence' => $sequence,
			'errors' => $errors,
		));
	}

	public function actionViewSessions()
	{
		if (@$_GET['reset'] == 1) {
			unset($_GET['reset']);
			unset(Yii::app()->session['admin_sessions']);
			$this->redirectWith($_GET);
		}

		if (empty($_GET) && !empty(Yii::app()->session['admin_sessions'])) {
			$this->redirectWith(Yii::app()->session['admin_sessions']);
		} elseif (!empty($_GET)) {
			Yii::app()->session['admin_sessions'] = $_GET;
		}

		Audit::add('admin','list',null,false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Session'));

		$this->render('/admin/sessions',array(
			'sessions' => $this->getSessions(),
		));
	}

	public function getSessions($all=false)
	{
		$criteria = new CDbCriteria;

		if ($firm = Firm::model()->findByPk(@$_REQUEST['firm_id'])) {
			$criteria->addCondition('t.firm_id=:firm_id');
			$criteria->params[':firm_id'] = $firm->id;
		} elseif (@$_REQUEST['firm_id'] == 'NULL') {
			$criteria->addCondition('t.firm_id is null');
		}

		if ($theatre = OphTrOperationbooking_Operation_Theatre::model()->findByPk(@$_REQUEST['theatre_id'])) {
			$criteria->addCondition('t.theatre_id=:theatre_id');
			$criteria->params[':theatre_id'] = $theatre->id;
		}

		if (@$_REQUEST['date_from'] && strtotime(@$_REQUEST['date_from'])) {
			$criteria->addCondition('date >= :start_date');
			$criteria->params[':start_date'] = date('Y-m-d',strtotime(@$_REQUEST['date_from']));
		}

		if (@$_REQUEST['date_to'] && strtotime(@$_REQUEST['date_to'])) {
			$criteria->addCondition('date <= :end_date');
			$criteria->params[':end_date'] = date('Y-m-d',strtotime(@$_REQUEST['date_to']));
		}

		if (@$_REQUEST['weekday'] != '') {
			$criteria->addCondition('sequence.weekday = :weekday');
			$criteria->params[':weekday'] = @$_REQUEST['weekday'];
		}

		if (@$_REQUEST['consultant'] != '') {
			$criteria->addCondition('t.consultant = :consultant');
			$criteria->params[':consultant'] = @$_REQUEST['consultant'];
		}

		if (@$_REQUEST['paediatric'] != '') {
			$criteria->addCondition('t.paediatric = :paediatric');
			$criteria->params[':paediatric'] = @$_REQUEST['paediatric'];
		}

		if (@$_REQUEST['anaesthetist'] != '') {
			$criteria->addCondition('t.anaesthetist = :anaesthetist');
			$criteria->params[':anaesthetist'] = @$_REQUEST['anaesthetist'];
		}

		if (@$_REQUEST['general_anaesthetic'] != '') {
			$criteria->addCondition('t.general_anaesthetic = :general_anaesthetic');
			$criteria->params[':general_anaesthetic'] = @$_REQUEST['general_anaesthetic'];
		}

		if (@$_REQUEST['available'] != '') {
			$criteria->addCondition('t.available = :available');
			$criteria->params[':available'] = @$_REQUEST['available'];
		}

		if (@$_REQUEST['sequence_id'] != '') {
			$criteria->addCondition('t.sequence_id = :sequence_id');
			$criteria->params[':sequence_id'] = @$_REQUEST['sequence_id'];
		}

		$page = @$_REQUEST['page'] ? $_REQUEST['page'] : 1;

		if ($all) {
			return OphTrOperationbooking_Operation_Session::model()->with('sequence')->findAll($criteria);
		}

		$count = OphTrOperationbooking_Operation_Session::model()->with('sequence')->count($criteria);
		$pages = ceil($count/$this->sessions_items_per_page);

		if ($page <1) $page = 1;
		if ($page > $pages) $page = $pages;

		$criteria->limit = $this->sessions_items_per_page;
		$criteria->offset = ($page-1) * $this->sessions_items_per_page;

		$order = @$_REQUEST['order']=='desc' ? 'desc' : 'asc';

		switch (@$_REQUEST['sortby']) {
			case 'firm':
				$criteria->order = "firm.name $order, subspecialty.name $order";
				break;
			case 'theatre':
				$criteria->order = "theatre.name $order";
				break;
			case 'dates':
				$criteria->order = "date $order, t.start_time $order, t.end_time $order";
				break;
			case 'time':
				$criteria->order = "t.start_time $order, t.end_time $order";
				break;
			case 'interval':
				$criteria->order = "interval.name $order";
				break;
			case 'weekday':
				$criteria->order = "sequence.weekday $order";
				break;
			default:
				$criteria->order = "firm.name $order, subspecialty.name $order";
		}

		$with = array(
			'sequence',
			'firm' => array(
				'with' => array(
					'serviceSubspecialtyAssignment' => array(
						'with' => 'subspecialty',
					),
				),
			),
			'theatre',
		);

		$this->items_per_page = $this->sessions_items_per_page;
		$pagination = $this->initPagination(OphTrOperationbooking_Operation_Session::model()->with($with), $criteria);
		$data = OphTrOperationbooking_Operation_Session::model()->with($with)->findAll($criteria);

		return array(
			'data' => $data,
			'pagination' => $pagination
		);
	}

	public function actionSessionInlineEdit()
	{
		$errors = array();

		if (!empty($_POST['session'])) {
			$criteria = new CDbCriteria;
			$criteria->addInCondition('id',$_POST['session']);
			$sessions = OphTrOperationbooking_Operation_Session::model()->findAll($criteria);
		} elseif (@$_POST['use_filters']) {
			$sessions = $this->getSessions(true);
		}

		$result = $this->saveSessions($sessions);

		if (empty($result['errors'])) {
			foreach ($result['sessions'] as $session) {
				if (!$session->save()) {
					echo json_encode($session->getErrors());
					return;
				}
				Audit::add('admin','update',serialize($session->getAuditAttributes()),false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Session'));
			}
			echo json_encode(array());
		} else {
			echo json_encode($result['errors']);
		}
	}

	public function saveSessions($sessions)
	{
		$errors = array();
		$_sessions = array();

		foreach ($sessions as $session) {
			$changed = false;

			foreach (array('firm_id','theatre_id','start_time','end_time','consultant','paediatric','anaesthetist','general_anaesthetic','comments','available') as $field) {
				if ($_POST['inline_'.$field] != '') {
					if ($session->$field != $_POST['inline_'.$field]) {
						$session->$field = $_POST['inline_'.$field];
						$changed = true;
					}
				}
			}
			if ($_POST['inline_date'] != '') {
				if (!strtotime($_POST['inline_date'])) {
					$errors['date'] = "Invalid start date";
				}
				if ($session->date != date('Y-m-d',strtotime($_POST['inline_date']))) {
					$session->date = date('Y-m-d',strtotime($_POST['inline_date']));
					$changed = true;
				}
			}

			if ($changed) {
				if (!$session->validate()) {
					$errors = array_merge($errors,$session->getErrors());
				} else {
					$_sessions[] = $session;
				}
			}
		}

		return array(
			'sessions' => $_sessions,
			'errors' => $errors,
		);
	}

	public function actionEditSession($id)
	{
		if (!$session = OphTrOperationbooking_Operation_Session::model()->findByPk($id)) {
			throw new Exception("Session not found: $id");
		}

		$errors = array();

		if (!empty($_POST)) {
			$session->attributes = $_POST['OphTrOperationbooking_Operation_Session'];

			if (!$session->save()) {
				$errors = $session->getErrors();
			} else {
				if (empty($errors)) {
					Audit::add('admin','update',serialize(array_merge(array('id'=>$id),$_POST)),false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Session'));
					$this->redirect(array('/OphTrOperationbooking/admin/viewSessions'));
				}
			}
		}

		Audit::add('admin','view',$id,false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Session'));

		$this->render('/admin/editsession',array(
			'session' => $session,
			'errors' => $errors,
		));
	}

	public function actionAddSession()
	{
		$session = new OphTrOperationbooking_Operation_Session;

		$errors = array();

		if (!empty($_POST)) {
			$session->attributes = $_POST['OphTrOperationbooking_Operation_Session'];

			if (!$session->save()) {
				$errors = $session->getErrors();
			} else {
				if (empty($errors)) {
					Audit::add('admin','create',serialize($_POST),false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Session'));
					$this->redirect(array('/OphTrOperationbooking/admin/viewSessions'));
				}
			}
		} elseif (isset($_GET['sequence_id'])) {
			$session->sequence_id = $_GET['sequence_id'];
		}

		Audit::add('admin','view',serialize($_POST),false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Session'));

		$this->render('/admin/editsession',array(
			'session' => $session,
			'errors' => $errors,
		));
	}

	public function actionVerifyDeleteSessions()
	{
		if (!empty($_POST['session'])) {
			$session_ids = $_POST['session'];
		} elseif (@$_POST['use_filters']) {
			$session_ids = array();
			foreach ($this->getSessions(true) as $session) {
				$session_ids[] = $session->id;
			}
		}

		$criteria = new CDbCriteria;
		$criteria->addInCondition('t.session_id',$session_ids);
		$criteria->addCondition('booking_cancellation_date is null');

		if (OphTrOperationbooking_Operation_Booking::model()
			->with(array(
				'operation' => array(
					'with' => array(
						'event' => array(
							'with' => 'episode',
						),
					),
				),
			))
			->find($criteria)) {
			echo "0";
		} else {
			echo "1";
		}
	}

	public function actionDeleteSessions()
	{
		if (!empty($_POST['session'])) {
			$criteria = new CDbCriteria;
			$criteria->addInCondition('id',$_POST['session']);
			$sessions = OphTrOperationbooking_Operation_Session::model()->findAll($criteria);
		} elseif (@$_POST['use_filters']) {
			$sessions = $this->getSessions(true);
		}

		foreach ($sessions as $session) {
			$session->deleted = 1;
			if (!$session->save()) {
				throw new Exception("Unable to mark session deleted: ".print_r($session->getErrors(),true));
			}
			Audit::add('admin','delete',$session->id,false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Session'));
		}

		echo "1";
	}

	public function actionVerifyDeleteSequences()
	{
		if (!empty($_POST['sequence'])) {
			$sequence_ids = $_POST['sequence'];
		} elseif (@$_POST['use_filters']) {
			$sequence_ids = array();
			foreach ($this->getSequences(true) as $sequence) {
				$sequence_ids[] = $sequence->id;
			}
		}

		$criteria = new CDbCriteria;
		$criteria->addInCondition('session.sequence_id',$sequence_ids);
		$criteria->addCondition('booking_cancellation_date is null');

		if (OphTrOperationbooking_Operation_Booking::model()
			->with(array(
				'session',
				'operation' => array(
					'with' => array(
						'event' => array(
							'with' => 'episode',
						),
					),
				),
			))
			->find($criteria)) {
			echo "0";
		} else {
			echo "1";
		}
	}

	public function actionDeleteSequences()
	{
		if (!empty($_POST['sequence'])) {
			$criteria = new CDbCriteria;
			$criteria->addInCondition('id',$_POST['sequence']);
			$sequences = OphTrOperationbooking_Operation_Sequence::model()->findAll($criteria);
		} elseif (@$_POST['use_filters']) {
			$sequences = $this->getSequences(true);
		}

		foreach ($sequences as $sequence) {
			$sequence->deleted = 1;
			if (!$sequence->save()) {
				throw new Exception("Unable to mark sequence deleted: ".print_r($sequence->getErrors(),true));
			}

			foreach ($sequence->sessions as $session) {
				$session->deleted = 1;
				if (!$session->save()) {
					throw new Exception("Unable to mark session deleted: ".print_r($session->getErrors(),true));
				}
			}

			Audit::add('admin','delete',$sequence->id,false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Sequence'));
		}

		echo "1";
	}

	public function actionViewTheatres()
	{
		Audit::add('admin','list',null,false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Theatre'));

		$this->render('theatres');
	}

	public function actionAddTheatre()
	{
		$errors = array();

		$theatre = new OphTrOperationbooking_Operation_Theatre;

		if (!empty($_POST)) {
			$theatre->attributes = $_POST['OphTrOperationbooking_Operation_Theatre'];
			if (!$theatre->save()) {
				$errors = $theatre->getErrors();
			} else {
				Audit::add('admin','create',serialize($_POST),false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Theatre'));
				$this->redirect(array('/OphTrOperationbooking/admin/viewTheatres'));
			}
		}

		$this->render('/admin/edittheatre',array(
			'theatre' => $theatre,
			'errors' => $errors,
		));
	}

	public function actionEditTheatre($id)
	{
		if (!$theatre = OphTrOperationbooking_Operation_Theatre::model()->findByPk($id)) {
			throw new Exception("Theatre not found: $id");
		}

		$errors = array();

		if (!empty($_POST)) {
			$theatre->attributes = $_POST['OphTrOperationbooking_Operation_Theatre'];
			if (!$theatre->save()) {
				$errors = $theatre->getErrors();
			} else {
				Audit::add('admin','update',serialize(array_merge(array('id'=>$id),$_POST)),false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Theatre'));

				$this->redirect(array('/OphTrOperationbooking/admin/viewTheatres'));
			}
		}

		Audit::add('admin','view',$id,false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Theatre'));

		$this->render('/admin/edittheatre',array(
			'theatre' => $theatre,
			'errors' => $errors,
		));
	}

	public function actionVerifyDeleteTheatres()
	{
		$criteria = new CDbCriteria;
		$criteria->addInCondition('session.theatre_id',$_POST['theatre']);
		$criteria->addCondition('booking_cancellation_date is null');
		$criteria->addCondition('session_date >= :today');
		$criteria->params[':today'] = date('Y-m-d');

		if (OphTrOperationbooking_Operation_Booking::model()
			->with(array(
				'session',
				'operation' => array(
					'with' => array(
						'event' => array(
							'with' => 'episode',
						),
					),
				),
			))
			->find($criteria)) {
			echo "0";
		} else {
			echo "1";
		}
	}

	public function actionDeleteTheatres()
	{
		$criteria = new CDbCriteria;
		$criteria->addInCondition('id',$_POST['theatre']);
		$theatres = OphTrOperationbooking_Operation_Theatre::model()->findAll($criteria);

		foreach ($theatres as $theatre) {
			$theatre->deleted = 1;
			if (!$theatre->save()) {
				throw new Exception("Unable to mark theatre deleted: ".print_r($theatre->getErrors(),true));
			}
			Audit::add('admin','delete',serialize($_POST['theatre']),false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Theatre'));
		}

		echo "1";
	}

	public function actionViewWards()
	{
		Audit::add('admin','list',null,false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Ward'));

		$this->render('wards');
	}

	public function actionEditWard($id)
	{
		if (!$ward = OphTrOperationbooking_Operation_Ward::model()->findByPk($id)) {
			throw new Exception("Ward not found: $id");
		}

		$errors = array();

		if (!empty($_POST)) {
			$ward->attributes = $_POST['OphTrOperationbooking_Operation_Ward'];

			$ward->restriction = 0;

			if (@$_POST['OphTrOperationbooking_Operation_Ward']['restriction_male']) {
				$ward->restriction += OphTrOperationbooking_Operation_Ward::RESTRICTION_MALE;
			}
			if (@$_POST['OphTrOperationbooking_Operation_Ward']['restriction_female']) {
				$ward->restriction += OphTrOperationbooking_Operation_Ward::RESTRICTION_FEMALE;
			}
			if (@$_POST['OphTrOperationbooking_Operation_Ward']['restriction_child']) {
				$ward->restriction += OphTrOperationbooking_Operation_Ward::RESTRICTION_CHILD;
			}
			if (@$_POST['OphTrOperationbooking_Operation_Ward']['restriction_adult']) {
				$ward->restriction += OphTrOperationbooking_Operation_Ward::RESTRICTION_ADULT;
			}
			if (@$_POST['OphTrOperationbooking_Operation_Ward']['restriction_observation']) {
				$ward->restriction += OphTrOperationbooking_Operation_Ward::RESTRICTION_OBSERVATION;
			}
			if (!$ward->save()) {
				$errors = $ward->getErrors();
			} else {
				Audit::add('admin','update',serialize(array_merge(array('id'=>$id),$_POST)),false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Ward'));

				$this->redirect(array('/OphTrOperationbooking/admin/viewWards'));
			}
		}

		Audit::add('admin','view',$id,false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Ward'));

		$this->render('/admin/editward',array(
			'ward' => $ward,
			'errors' => $errors,
		));
	}

	public function actionAddWard()
	{
		$errors = array();

		$ward = new OphTrOperationbooking_Operation_Ward;

		if (!empty($_POST)) {
			$ward->attributes = $_POST['OphTrOperationbooking_Operation_Ward'];

			if (!$ward->save()) {
				$errors = $ward->getErrors();
			} else {
				Audit::add('admin','create',serialize($_POST),false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_Operation_Ward'));

				$this->redirect(array('/OphTrOperationbooking/admin/viewWards'));
			}
		}

		$this->render('/admin/editward',array(
			'ward' => $ward,
			'errors' => $errors,
		));
	}

	public function actionViewSchedulingOptions()
	{
		Audit::add('admin','list',null,false,array('module'=>'OphTrOperationbooking','model'=>'OphTrOperationbooking_ScheduleOperation_Options'));

		$this->render('schedulingoptions');
	}

	public function actionVerifyDeleteSchedulingOptions()
	{
		$criteria = new CDbCriteria;
		$criteria->addInCondition('schedule_options_id',$_POST['scheduleoption']);

		if (Element_OphTrOperationbooking_ScheduleOperation::model()
			->with(array(
				'event' => array(
					'with' => 'episode',
				),
			))
			->find($criteria)) {
			echo "0";
		} else {
			echo "1";
		}
	}

	public function actionDeleteSchedulingOptions()
	{
		$criteria = new CDbCriteria;
		$criteria->addInCondition('id',$_POST['scheduleoption']);
		$options = OphTrOperationbooking_ScheduleOperation_Options::model()->findAll($criteria);

		foreach ($options as $option) {
			if (!$option->delete()) {
				throw new Exception("Unable to delete scheduling option: ".print_r($option->getErrors(),true));
			}
			Audit::add('admin','delete',$option->id,false,array('OphTrOperationbooking','model'=>'OphTrOperationbooking_ScheduleOperation_Options'));
		}

		echo "1";
	}

	public function actionEditSchedulingOption($id)
	{
		if (!$option = OphTrOperationbooking_ScheduleOperation_Options::model()->findByPk($id)) {
			throw new Exception("Ward not found: $id");
		}

		$errors = array();

		if (!empty($_POST)) {
			$option->attributes = $_POST['OphTrOperationbooking_ScheduleOperation_Options'];

			if (!$option->save()) {
				$errors = $option->getErrors();
			} else {
				Audit::add('admin','update',serialize(array_merge(array('id'=>$id),$_POST)),false,array('OphTrOperationbooking','model'=>'OphTrOperationbooking_ScheduleOperation_Options'));

				$this->redirect(array('/OphTrOperationbooking/admin/viewSchedulingOptions'));
			}
		}

		Audit::add('admin','view',$id,false,array('OphTrOperationbooking','model'=>'OphTrOperationbooking_ScheduleOperation_Options'));

		$this->render('/admin/editschedulingoption',array(
			'option' => $option,
			'errors' => $errors,
		));
	}

	public function actionAddSchedulingOption()
	{
		$errors = array();

		$option = new OphTrOperationbooking_ScheduleOperation_Options;

		if (!empty($_POST)) {
			$option->attributes = $_POST['OphTrOperationbooking_ScheduleOperation_Options'];
			if (!$option->save()) {
				$errors = $option->getErrors();
			} else {
				Audit::add('admin','create',serialize($_POST),false,array('OphTrOperationbooking','model'=>'OphTrOperationbooking_ScheduleOperation_Options'));
				$this->redirect(array('/OphTrOperationbooking/admin/viewSchedulingOptions'));
			}
		}

		$this->render('/admin/editschedulingoption',array(
			'option' => $option,
			'errors' => $errors,
		));
	}
}
