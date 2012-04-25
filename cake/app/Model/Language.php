<?php
App::uses('AppModel', 'Model');
/**
 * Language Model
 *
 */
class Language extends AppModel {
/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'language';
/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'lang_name';
}
