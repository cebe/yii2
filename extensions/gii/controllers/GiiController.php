<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\controllers;

use Yii;
use yii\console\Controller;
use yii\gii\Generator;
use yii\gii\Module;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * This command allows you to generate code with gii code generator
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class GiiController extends Controller
{
	/**
	 * @var string controller default action ID.
	 */
	public $defaultAction = 'generate';

	/**
	 * @param $generator
	 * @return int
	 */
	public function actionGenerate($generator = null)
	{
		$this->printHeader();

		if ($generator === null) {
			$generator = $this->chooseGenerator();
			$this->stdout("\n");
		}
		if (!isset($this->module->generators[$generator])) {
			$this->stderr("Generator '$generator' does not exist.\n", Console::FG_RED);
			return 1;
		} else {
			/** @var Generator $generator */
			$generator = $this->module->generators[$generator];
		}

		$this->stdout("Running {$generator->name}...\n\n", Console::BOLD);
		$this->stdout("Please enter the parameters for this generator:\n");

		$attributes = $generator->activeAttributes();
top:
		foreach($attributes as $i => $attribute) {
			if ($attribute == 'template') {
				unset($attributes[$i]);
				continue;
			}
			$this->stdout("\n");
			$this->displayErrors($generator, $attribute);
			$this->displayHint($generator, $attribute);
			$generator->$attribute = $this->prompt($generator->getAttributeLabel($attribute), ['default' => $generator->$attribute]);
		}

		if (!$generator->validate($attributes)) {
			$attributes = array_keys($generator->getErrors());
			$this->stdout("\n");
			goto top;
		}

		if (count($generator->templates) == 1) {
			$generator->template = key($generator->templates);
		} else {
			$generator->template = $this->select('Select template', $generator->templates);
		}
		if (!$generator->validate($attributes)) {
			$this->stderr($generator->getFirstError('template') . "\n", Console::FG_RED);
			return 1;
		}

		$generator->saveStickyAttributes();

		$files = $generator->generate();

		$this->stdout("\nThe following files will be generated:\n");
		foreach($files as $file) {
			$this->stdout($file->path . "\n");
		}

decision:
		$option = $this->prompt('generate(g), preview(p) or abort(a)?', [
			'required' => true,
			'validator' => function($input) {
				return in_array($input, ['g', 'p', 'a']);
			}
		]);

		if ($option == 'a') {
			return 0;
		} elseif ($option == 'g') {
			foreach($files as $file) {
//			$params['hasError'] = $generator->save($files, (array)$_POST['answers'], $results);
				$this->stdout('saving ' . $file->path . "\n"); // ask if exists, show diff if exists
			}
		} elseif ($option == 'p') {
			foreach($files as $file) {
				if ($this->confirm('Preview ' . $file->path . '?')) {
					$this->stdout($file->content . "\n");
				}
			}
			goto decision;
		}
	}

	protected function displayErrors($generator, $attribute)
	{
		$errors = $generator->getErrors($attribute);
		if (!empty($errors)) {
			foreach($errors as $error) {
				$this->stdout($error . "\n", Console::FG_RED);
			}
		}
	}

	protected function displayHint($generator, $attribute)
	{
		$hints = $generator->hints();
		if (isset($hints[$attribute])) {
			$hint = $hints[$attribute];
			$hint = preg_replace_callback('/<code>(.+?)<\/code>/i', function($matches) {
				return $this->ansiFormat($matches[1], Console::FG_CYAN);
			}, $hint);
			$this->stdout($hint . "\n");
		}
	}

	/**
	 * List all available generators
	 */
	public function chooseGenerator()
	{
		$generators = $this->module->generators;
		$this->stdout("Here is a list of available generators:\n\n");
		$width = 2;
		foreach ($generators as $id => $generator) {
			$len = mb_strlen($id);
			if ($width < $len) {
				$width = $len;
			}
		}
		$width += 2;

		list($w, $h) = Console::getScreenSize();
		if ($w > 120) {
			$w = 120;
		}

		foreach ($generators as $id => $generator) {
			$this->stdout(' - ' . $id, Console::BOLD);
			$this->stdout(str_repeat(' ', $width - strlen($id)) . $generator->getName() . "\n", Console::BOLD);
			$this->stdout(str_repeat(' ', $width + 3));
			$this->stdout(preg_replace("/\n\s*/", "\n" . str_repeat(' ', $width + 3), wordwrap($generator->getDescription(), $width < $w ? $w - $width - 3 : 75)) . "\n\n");
		}

		$generatorNames = array_keys($generators);
		//ArrayHelper::getColumn($generators, 'name')
		return Console::prompt('choose generator', [
			'required' => true,
			'error' => 'Choose a validator from the list!',
			'validator' => function($input, &$error) use ($generatorNames) {
				return in_array($input, $generatorNames);
			}
		]);
	}

	public function printHeader()
	{
		Console::beginAnsiFormat([Console::BOLD, Console::FG_GREY]);
		echo "Welcome to";
		Console::beginAnsiFormat([Console::FG_GREEN]);
		echo
		<<<EOF
  _   _
     __ _  (_) (_)
    / _` | | | | |
   | (_| | | | | |
    \__, | |_| |_|
    |___/
EOF;
		Console::beginAnsiFormat([Console::BOLD, Console::FG_GREY]);
		echo "  a magic tool that can write code for you!\n";
		Console::endAnsiFormat();
		echo "\n\n";
	}
}
