<?php

class m130917_124929_operation_booking_shortcode extends CDbMigration
{
	public function up()
	{
		$event_type = EventType::model()->find('class_name=?',array('OphTrOperationbooking'));
		$this->insert('patient_shortcode',array('event_type_id'=>$event_type->id,'default_code'=>'obd','code'=>'obd','description'=>'The latest operation booking diagnosis','method'=>'getLatestOperationBookingDiagnosis'));
	}

	public function down()
	{
		$this->delete('patient_shortcode', "default_code = 'obd'");
	}
}
