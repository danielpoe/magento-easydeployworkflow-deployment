<?php

namespace EasyDeployWorkflows\Source\File;
use EasyDeployWorkflows\Source\SourceInterface;

/**
 * FileSource Interface.
 * A FileSource is normaly an archive
 */
interface FileSourceInterface extends SourceInterface {

	/**
	 * @return string
	 */
	public function getFileName();

	/**
	 * @return string
	 */
	public function getFileNameWithOutExtension();

	/**
	 * @return string
	 */
	public function getFolderNameInArchive();

}
