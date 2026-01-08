<?php

/**
 * somoim
 *
 * Copyright (c) redsgo
 *
 * Generated with https://www.poesis.dev/tools/modulegen
 */
class SomoimView extends Somoim
{
	/**
	 * 초기화
	 */
	public function init()
	{
		// 스킨 경로 지정
		$this->setTemplatePath($this->module_path . 'skins/' . ($this->module_info->skin ?: 'default'));
	}

	/**
	 * 메인 화면 예제
	 */
	public function dispSomoimIndex()
	{
		// 스킨 파일명 지정
		$this->setTemplateFile('index');
	}
}
