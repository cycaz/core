<?php
class TMC_Model_Translate_Adapter_Database extends \Phalcon\Translate\Adapter implements \Phalcon\Translate\AdapterInterface
{

	protected $_options;

	/**
	 * TMC_Model_Translate_Adapter_Database constructor
	 *
	 * @param array $options
	 */
	public function __construct($options)
	{

		if (!isset($options['db'])) {
			throw new Exception("Parameter 'db' is required");
		}

		if (!isset($options['table'])) {
			throw new Exception("Parameter 'table' is required");
		}

		$this->_options = $options;
	}

	/**
	 * Returns the translation related to the given key
	 *
	 * @param	string $index
	 * @param	array $placeholders
	 * @return	string
	 */
	public function query($index, $placeholders=null)
	{

		$options = $this->_options;

		$translation = $options['db']->fetchOne("SELECT value FROM " . $options['table'] . " WHERE key_name = ?", null, array($index));
		if (!$translation) {
			return $index;
		}

		if ($placeholders == null) {
			return $translation['value'];
		}

		if (is_array($placeholders)) {
			foreach ($placeholders as $key => $value) {
				$translation['value'] = str_replace('%' . $key . '%', $value, $translation['value']);
			}
		}

		return $translation['value'];
	}

	/**
	 * Check whether is defined a translation key in the database
	 *
	 * @param 	string $index
	 * @return	bool
	 */
	public function exists($index)
	{
		$exists = $options['db']->fetchOne("SELECT COUNT(*) FROM " . $options['table'] . " WHERE key_name = ?0", null, array($index));
		return $exists[0] > 0;
	}

}