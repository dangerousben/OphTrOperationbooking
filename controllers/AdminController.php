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

class AdminController extends ModuleAdminController {
	public $sequences_items_per_page = 20;
	public $sessions_items_per_page = 20;

	public function actionViewERODRules() {
		$this->render('erodrules');
	}

	public function actionCreatePostOpDrug() {
		if (empty($_POST['name'])) {
			throw new Exception("Missing name");
		}

		if ($drug = PostopDrug::model()->find(array('order'=>'display_order desc'))) {
			$display_order = $drug->display_order+1;
		} else {
			$display_order = 1;
		}

		$drug = new PostopDrug;
		$drug->name = @$_POST['name'];
		$drug->display_order = $display_order;

		if (!$drug->save()) {
			echo json_encode(array('errors'=>$drug->getErrors()));
			return;
		}

		// TODO: this is a hack for the Orbis demo and should be removed when full site/subspecialty functionality has been implemented
		$specialty = Specialty::model()->find('code=?',array('OPH'));
		foreach (Site::model()->findAll('institution_id=?',array(1)) as $site) {
			foreach (Subspecialty::model()->findAll('specialty_id=?',array($specialty->id)) as $subspecialty) {
				$ssd = new PostopSiteSubspecialtyDrug;
				$ssd->site_id = $site->id;
				$ssd->subspecialty_id = $subspecialty->id;
				$ssd->drug_id = $drug->id;
				if (!$ssd->save()) {
					echo json_encode(array('errors'=>$ssd->getErrors()));
				}
			}
		}

		echo json_encode(array('id'=>$drug->id,'errors'=>array()));
	}

	public function actionUpdatePostOpDrug() {
		if (!$drug = PostopDrug::model()->findByPk(@$_POST['id'])) {
			throw new Exception("Drug not found: ".@$_POST['id']);
		}

		$drug->name = @$_POST['name'];
		if (!$drug->save()) {
			echo json_encode(array('errors'=>$drug->getErrors()));
			return;
		}

		echo json_encode(array('errors'=>array()));
	}

	public function actionDeletePostOpDrug($id) {
		if ($drug = PostopDrug::model()->findByPk($id)) {
			$drug->deleted = 1;
			if ($drug->save()) {
				echo "1";
				return;
			}
		}
		echo "0";
	}

	public function actionSortPostOpDrugs() {
		if (!empty($_POST['order'])) {
			foreach ($_POST['order'] as $i => $id) {
				if ($drug = PostopDrug::model()->findByPk($id)) {
					$drug->display_order = $i+1;
					if (!$drug->save()) {
						throw new Exception("Unable to save drug: ".print_r($drug->getErrors(),true));
					}
				}
			}
		}
	}

	public function actionEditERODRule($id) {
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
					$this->redirect(array('/OphTrOperationbooking/admin/viewERODRules'));
				}
			}
		}

		$this->render('/admin/editerodrule',array(
			'erod' => $erod,
			'errors' => $errors,
		));
	}

	public function actionAddERODRule() {
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
					$this->redirect(array('/OphTrOperationbooking/admin/viewERODRules'));
				}
			}
		}

		$this->render('/admin/editerodrule',array(
			'erod' => $erod,
			'errors' => $errors,
		));
	}

	public function actionDeleteERODRules() {
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
		}

		echo "1";
	}

	public function actionViewLetterContactRules() {
		$this->jsVars['OE_rule_model'] = 'LetterContactRule';

		$this->render('lettercontactrules',array(
			'data' => OphTrOperationbooking_Letter_Contact_Rule::model()->findAllAsTree(),
		));
	}

	public function actionTestLetterContactRules() {
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

	public function actionEditLetterContactRule($id) {
		if (!$rule = OphTrOperationbooking_Letter_Contact_Rule::model()->findByPk($id)) {
			throw new Exception("Letter contact rule not found: $id");
		}

		$errors = array();

		if (!empty($_POST)) {
			$rule->attributes = $_POST['OphTrOperationbooking_Letter_Contact_Rule'];

			if (!$rule->save()) {
				$errors = $rule->getErrors();
			} else {
				$this->redirect(array('/OphTrOperationbooking/admin/viewLetterContactRules'));
			}
		}

		$this->jsVars['OE_rule_model'] = 'LetterContactRule';

		$this->render('editlettercontactrule',array(
			'rule' => $rule,
			'errors' => $errors,
		));
	}

	public function actionDeleteLetterContactRule($id) {
		if (!$rule = OphTrOperationbooking_Letter_Contact_Rule::model()->findByPk($id)) {
			throw new Exception("Letter contact rule not found: $id");
		}

		$errors = array();

		if (!empty($_POST)) {
			if (@$_POST['delete']) {
				if (!$rule->delete()) {
					$errors = $rule->getErrors();
				} else {
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

	public function actionAddLetterContactRule() {
		$rule = new OphTrOperationbooking_Letter_Contact_Rule;

		$errors = array();

		if (!empty($_POST)) {
			$rule->attributes = $_POST['OphTrOperationbooking_Letter_Contact_Rule'];

			if (!$rule->save()) {
				$errors = $rule->getErrors();
			} else {
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

	public function actionViewLetterWarningRules() {
		$this->jsVars['OE_rule_model'] = 'LetterWarningRule';

		$this->render('letterwarningrules',array(
			'data' => OphTrOperationbooking_Admission_Letter_Warning_Rule::model()->findAllAsTree(),
		));
	}

	public function actionTestLetterWarningRules() {
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

	public function actionEditLetterWarningRule($id) {
		if (!$rule = OphTrOperationbooking_Admission_Letter_Warning_Rule::model()->findByPk($id)) {
			throw new Exception("Letter warning rule not found: $id");
		}

		$errors = array();

		if (!empty($_POST)) {
			$rule->attributes = $_POST['OphTrOperationbooking_Admission_Letter_Warning_Rule'];

			if (!$rule->save()) {
				$errors = $rule->getErrors();
			} else {
				$this->redirect(array('/OphTrOperationbooking/admin/viewLetterWarningRules'));
			}
		}

		$this->jsVars['OE_rule_model'] = 'LetterWarningRule';

		$this->render('editletterwarningrule',array(
			'rule' => $rule,
			'errors' => $errors,
		));
	}

	public function actionAddLetterWarningRule() {
		$rule = new OphTrOperationbooking_Admission_Letter_Warning_Rule;

		$errors = array();

		if (!empty($_POST)) {
			$rule->attributes = $_POST['OphTrOperationbooking_Admission_Letter_Warning_Rule'];

			if (!$rule->save()) {
				$errors = $rule->getErrors();
			} else {
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

	public function actionDeleteLetterWarningRule($id) {
		if (!$rule = OphTrOperationbooking_Admission_Letter_Warning_Rule::model()->findByPk($id)) {
			throw new Exception("Letter warning rule not found: $id");
		}

		$errors = array();

		if (!empty($_POST)) {
			if (@$_POST['delete']) {
				if (!$rule->delete()) {
					$errors = $rule->getErrors();
				} else {
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

	public function actionViewWaitingListContactRules() {
		$this->jsVars['OE_rule_model'] = 'WaitingListContactRule';

		$this->render('waitinglistcontactrules',array(
			'data' => OphTrOperationbooking_Waiting_List_Contact_Rule::model()->findAllAsTree(),
		));
	}

	public function actionTestWaitingListContactRules() {
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

	public function actionEditWaitingListContactRule($id) {
		if (!$rule = OphTrOperationbooking_Waiting_List_Contact_Rule::model()->findByPk($id)) {
			throw new Exception("Waiting list contact rule not found: $id");
		}

		$errors = array();

		if (!empty($_POST)) {
			$rule->attributes = $_POST['OphTrOperationbooking_Waiting_List_Contact_Rule'];

			if (!$rule->save()) {
				$errors = $rule->getErrors();
			} else {
				$this->redirect(array('/OphTrOperationbooking/admin/viewWaitingListContactRules'));
			}
		}

		$this->jsVars['OE_rule_model'] = 'WaitingListContactRule';

		$this->render('editwaitinglistcontactrule',array(
			'rule' => $rule,
			'errors' => $errors,
		));
	}

	public function actionDeleteWaitingListContactRule($id) {
		if (!$rule = OphTrOperationbooking_Waiting_List_Contact_Rule::model()->findByPk($id)) {
			throw new Exception("Waiting list contact rule not found: $id");
		}

		$errors = array();

		if (!empty($_POST)) {
			if (@$_POST['delete']) {
				if (!$rule->delete()) {
					$errors = $rule->getErrors();
				} else {
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

	public function actionViewOperationNameRules() {
		$this->render('operationnamerules');
	}

	public function actionAddOperationNameRule() {
		$errors = array();

		$rule = new OphTrOperationbooking_Operation_Name_Rule;

		if (!empty($_POST)) {
			$rule->attributes = $_POST['OphTrOperationbooking_Operation_Name_Rule'];

			if (!$rule->save()) {
				$errors = $erod->getErrors();
			} else {
				$this->redirect(array('/OphTrOperationbooking/admin/viewOperationNameRules'));
			}
		}

		$this->render('/admin/editoperationnamerule',array(
			'rule' => $rule,
			'errors' => $errors,
		));
	}

	public function actionEditOperationNameRule($id) {
		if (!$rule = OphTrOperationbooking_Operation_Name_Rule::model()->findByPk($id)) {
			throw new Exception("Operation name rule not found: $id");
		}

		$errors = array();

		if (!empty($_POST)) {
			$rule->attributes = $_POST['OphTrOperationbooking_Operation_Name_Rule'];

			if (!$rule->save()) {
				$errors = $rule->getErrors();
			} else {
				$this->redirect(array('/OphTrOperationbooking/admin/viewOperationNameRules'));
			}
		}

		$this->render('/admin/editoperationnamerule',array(
			'rule' => $rule,
			'errors' => $errors,
		));
	}

	public function actionDeleteOperationNameRules() {
		if (!empty($_POST['operation_name'])) {
			foreach ($_POST['operation_name'] as $rule_id) {
				if ($_rule = OphTrOperationbooking_Operation_Name_Rule::model()->findByPk($rule_id)) {
					if (!$_rule->delete()) {
						throw new Exception("Unable to delete rule rule: ".print_r($_rule->getErrors(),true));
					}
				}
			}
		}

		echo "1";
	}

	public function actionViewSequences() {
		if (@$_GET['reset'] == 1) {
			unset($_GET['reset']);
			unset(Yii::app()->session['admin_sequences']);
			$this->redirectWith($_GET);
		}

		if (empty($_GET) && !empty(Yii::app()->session['admin_sequences'])) {
			$this->redirectWith(Yii::app()->session['admin_sequences']);
		} else if (!empty($_GET)) {
			Yii::app()->session['admin_sequences'] = $_GET;
		}

		$this->render('/admin/sequences',array(
			'sequences' => $this->getSequences(),
		));
	}

	public function redirectWith($params) {
		$uri = preg_replace('/\?.*$/','',$_SERVER['REQUEST_URI']);

		$first=true;
		foreach ($params as $key => $value) {
			$uri .= $first ? '?' : '&';
			$first=false;
			$uri .= "$key=$value";
		}

		$this->redirect(array($uri));
	}

	public function getSequences($all=false) {
		$criteria = new CDbCriteria;

		if ($firm = Firm::model()->findByPk(@$_REQUEST['firm_id'])) {
			$criteria->addCondition('firm_id=:firm_id');
			$criteria->params[':firm_id'] = $firm->id;
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
			$criteria->addCondition('end_date >= :end_date');
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

		$data = OphTrOperationbooking_Operation_Sequence::model()->with(array(
				'firm' => array(
					'with' => array(
						'serviceSubspecialtyAssignment' => array(
							'with' => 'subspecialty',
						),
					),
				),
				'theatre',
				'interval',
			))->findAll($criteria);
			
		return array(
			'data' => $data,
			'count' => $count,
			'page' => $page,
			'pages' => $pages,
			'more_items' => ($count > count($data)),
			'items_per_page' => $this->sequences_items_per_page,
		);
	}

	public function getUri($elements) {
		$uri = preg_replace('/\?.*$/','',$_SERVER['REQUEST_URI']);

		if (isset($elements['sortby']) && $elements['sortby'] == @$_GET['sortby']) {
			$_GET['order'] = (@$_GET['order'] == 'desc') ? 'asc' : 'desc';
		} else if (isset($_GET['sortby']) && isset($elements['sortby']) && $_GET['sortby'] != $elements['sortby']) {
			$_GET['order'] = 'asc';
		}

		$first = true;
		foreach (array_merge($_GET,$elements) as $key => $value) {
			$uri .= $first ? '?' : '&';
			$first = false;
			$uri .= "$key=$value";
		}

		return $uri;
	}

	public function actionSequenceInlineEdit() {
		$errors = array();

		if (!empty($_POST['sequence'])) {
			$criteria = new CDbCriteria;
			$criteria->addInCondition('id',$_POST['sequence']);
			$sequences = OphTrOperationbooking_Operation_Sequence::model()->findAll($criteria);
		} else if (@$_POST['use_filters']) {
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
			}
		}

		echo json_encode($errors);
	}

	public function actionEditSequence($id) {
		if (!$sequence = OphTrOperationbooking_Operation_Sequence::model()->findByPk($id)) {
			throw new Exception("Sequence not found: $id");
		}

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
					$this->redirect(array('/OphTrOperationbooking/admin/viewSequences'));
				}
			}
		}

		$this->render('/admin/editsequence',array(
			'sequence' => $sequence,
			'errors' => $errors,
		));
	}

	public function actionAddSequence($id) {
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
					$this->redirect(array('/OphTrOperationbooking/admin/viewSequences'));
				}
			}
		}

		$this->render('/admin/editsequence',array(
			'sequence' => $sequence,
			'errors' => $errors,
		));
	}

	public function actionViewSessions() {
		if (@$_GET['reset'] == 1) {
			unset($_GET['reset']);
			unset(Yii::app()->session['admin_sessions']);
			$this->redirectWith($_GET);
		}

		if (empty($_GET) && !empty(Yii::app()->session['admin_sessions'])) {
			$this->redirectWith(Yii::app()->session['admin_sessions']);
		} else if (!empty($_GET)) {
			Yii::app()->session['admin_sessions'] = $_GET;
		}

		$this->render('/admin/sessions',array(
			'sessions' => $this->getSessions(),
		));
	}

	public function getSessions($all=false) {
		$criteria = new CDbCriteria;

		if ($firm = Firm::model()->findByPk(@$_REQUEST['firm_id'])) {
			$criteria->addCondition('firm_id=:firm_id');
			$criteria->params[':firm_id'] = $firm->id;
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
			$criteria->addCondition('date >= :end_date');
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

		$data = OphTrOperationbooking_Operation_Session::model()->with(array(
			'sequence',
			'firm' => array(
				'with' => array(
					'serviceSubspecialtyAssignment' => array(
						'with' => 'subspecialty',
					),
				),
			),
			'theatre',
		))->findAll($criteria);

		return array(
			'data' => $data,
			'count' => $count,
			'page' => $page,
			'pages' => $pages,
			'more_items' => ($count > count($data)),
			'items_per_page' => $this->sessions_items_per_page,
		);
	}

	public function actionSessionInlineEdit() {
		$errors = array();

		if (!empty($_POST['session'])) {
			$criteria = new CDbCriteria;
			$criteria->addInCondition('id',$_POST['session']);
			$sessions = OphTrOperationbooking_Operation_Session::model()->findAll($criteria);
		} else if (@$_POST['use_filters']) {
			$sessions = $this->getSessions(true);
		}

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
				if (!empty($errors)) {
					$session->validate();
					echo json_encode(array_merge($errors,$session->getErrors()));
					return;
				}

				if (!$session->save()) {
					echo json_encode($session->getErrors());
					return;
				}
			}
		}

		echo json_encode($errors);
	}

	public function actionEditSession($id) {
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
					$this->redirect(array('/OphTrOperationbooking/admin/viewSessions'));
				}
			}
		}

		$this->render('/admin/editsession',array(
			'session' => $session,
			'errors' => $errors,
		));
	}
}
