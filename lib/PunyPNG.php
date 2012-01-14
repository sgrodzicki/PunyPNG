<?php

/*
 * Copyright (c) 2011 Sebastian Grodzicki
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * PHP library for using the PunyPNG API
 *
 * @package		PunyPNG
 * @author         Sebastian Grodzicki <sebastian@grodzicki.pl>
 * @copyright      Copyright (c) 2011 Sebastian Grodzicki (http://sebastian.grodzicki.pl)
 *
 */
class PunyPNG
{
	/** PunyPNG API URI */
	const API_URI = 'http://www.punypng.com/api/optimize';

	/** Exception messages */
	const CURL           = 'cURL library required';
	const FILE_NOT_FOUND = 'File "%s" not found';
	const UNKOWN_ERROR   = 'Something went wrong';

	/**
	 * PunyPNG API key
	 *
	 * @var		string
	 * @access	protected
	 */
	protected $_apiKey;

	/**
	 * Constructs a new PunyPNG instance and checks dependencies.
	 *
	 * @param	string	$apiKey	Your PunyPNG API key
	 * @return	void
	 * @throws	Exception
	 */
	public function __construct($apiKey)
	{
		$this->_checkDependencies();
		$this->_setApiKey($apiKey);
	}

	/**
	 * Sets PunyPNG API key
	 *
	 * @param	string	PunyPNG API key
	 * @return	void
	 * @access	protected
	 */
	protected function _setApiKey($apiKey)
	{
		$this->_apiKey = $apiKey;
	}

	/**
	 * Checks dependencies
	 *
	 * @return	void
	 * @throws	Exception
	 * @access	protected
	 */
	protected function _checkDependencies()
	{
		if (!in_array('curl', get_loaded_extensions())) {
			throw new Exception(self::CURL);
		}
	}

	/**
	 * Optimizes the specified image file
	 *
	 * @param	string	$filepath	Path to image file
	 * @return	array	Compression information
	 * @throws	Exception
	 */
	public function optimize($filepath)
	{
		$this->_imageValidation($filepath);
		$data = $this->_sendRequest($filepath);

		return $data;
	}

	/**
	 * Validates the graphic file
	 *
	 * @param	string	$filepath	Path to image file
	 * @return	void
	 * @throws	Exception
	 */
	protected function _imageValidation($filepath)
	{
		if (!file_exists($filepath) || false === $file = file_get_contents($filepath)) {
			throw new Exception(sprintf(self::FILE_NOT_FOUND, $filepath));
		}
	}

	/**
	 * Sends a request to the PunyPNG API
	 *
	 * @param	string	$filepath	Path to image file
	 * @return	array	Compression information
	 * @throws	Exception
	 * @access	protected
	 */
	protected function _sendRequest($filepath)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::API_URI);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array(
			'key'	=> $this->getApiKey(),
			'img'	=> '@' . $filepath,
		));
		$response = curl_exec($ch);
		curl_close($ch);

		if (false === $data = json_decode($response, true)) {
			throw new Exception(self::UNKOWN_ERROR);
		}

		if (isset($data['error'])) {
			throw new Exception($data['error']);
		}

		return $data;
	}

	/**
	 * Returns PunyPNG API key
	 *
	 * @return	string	PunyPNG API key
	 */
	public function getApiKey()
	{
		return $this->_apiKey;
	}
}
