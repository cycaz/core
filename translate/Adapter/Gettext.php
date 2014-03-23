<?php
class TMC_Model_Translate_Adapter_Gettext extends \Phalcon\Translate\Adapter implements \Phalcon\Translate\AdapterInterface
{
	/**
	 * TMC_Model_Translate_Adapter_Gettext constructor
	 *
	 * @param array $options
	 */
	public function __construct($options)
	{
		if (!is_array($options)) {
			throw new Exception('Invalid options');
		}

		if (!isset($options['locale'])) {
			throw new Exception('Parameter "locale" is required');
		}

		if (!isset($options['file'])) {
			throw new Exception('Parameter "file" is required');
		}

		if (!isset($options['directory'])) {
			throw new Exception('Parameter "directory" is required');
		}

		putenv("LC_ALL=" . $options['locale']);
		setlocale(LC_ALL, $options['locale']);
		bindtextdomain($options['file'], $options['directory']);
		textdomain($options['file']);
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
		if ($placeholders == null) {
			return gettext($index);
		}

		$translation = gettext($index);;
		if (is_array($placeholders)) {
			foreach ($placeholders as $key => $value) {
				$translation = str_replace('%' . $key . '%', $value, $translation);
			}
		}

		return $translation;
	}

	/**
	 * Check whether is defined a translation key in gettext
	 *
	 * @param 	string $index
	 * @return	bool
	 */
	public function exists($index)
	{
		return gettext($index) !== '';
	}

}