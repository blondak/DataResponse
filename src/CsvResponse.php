<?php

namespace Blondak\DataResponse;

use Exception;
use InvalidArgumentException;
use Nette\Application\IResponse as NetteAppIReseponse;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Application\BadRequestException;


class CsvResponse implements NetteAppIReseponse
{
	private $dataSource;

	/**
	 * @var array
	 */
	private $header = [];

	/**
	 * @var string
	 */
	private $outputFilename;

	private $csvCharset;
	private $csvDelimiter;
	private $csvEnclosure;
	private $csvEscape;

	public function __construct(
		$dataSource,
		$outputFilename = 'output.csv',
	    $csvCharset = "UTF-8",
	    $csvDelimiter = ",",
	    $csvEnclosure = '"',
	    $csvEscape = "\\"
	) {
	    if (!(is_array($dataSource) || $dataSource instanceof \Traversable)){
	        throw new BadRequestException(__METHOD__);
	    }
		$this->dataSource = $dataSource;
		$this->outputFilename = $outputFilename;
		$this->csvCharset = $csvCharset;
		$this->csvDelimiter = $csvDelimiter;
		$this->csvEnclosure = $csvEnclosure;
		$this->csvEscape = $csvEscape;
	}

	public function setHeader(array $header){
		$this->header = $header;
		return $this;
	}

	public function setOutputFilename($outputFilename){
		$this->outputFilename = $outputFilename;
		return $this;
	}

	public function getOutputFilename(){
		return $this->outputFilename;
	}

	protected function convertEncoding($data){
	    if (strcasecmp($this->csvCharset, "utf-8") === 0){
 	        return $data;
 	    }
 	    $result = [];
	    foreach ($data as $field){
	        $result[] = iconv('utf-8', $this->csvCharset, $field);
	    }
	    return $result;
	}
	    
	public function send(IRequest $httpRequest, IResponse $httpResponse){
  		$httpResponse->setContentType('text/csv', $this->csvCharset);
 		$httpResponse->setHeader('Content-Disposition', 'attachment; filename="' . $this->outputFilename . '"');

 		$acceptEncoding = $httpRequest->getHeader('Accept-Encoding', '');
 		$supportsGzip = stripos($acceptEncoding, 'gzip' ) !== FALSE;
 		if ($supportsGzip) {
 			$httpResponse->setHeader('Content-Encoding', 'gzip');
 			ob_start('ob_gzhandler');
 		}

	    $buffer = new \SplFileObject('php://output', 'w');
	    
		if ($buffer === FALSE) {
			throw new Exception(sprintf('%s: error create buffer!', __CLASS__));
		}
		
		if (strcasecmp($this->csvCharset, "utf-8") === 0){
		    $buffer->fwrite($this->getUtf8BomBytes());
		}

		if (count($this->header)) {
		    $buffer->fputcsv((array)$this->convertEncoding($this->header), $this->csvDelimiter, $this->csvEnclosure, $this->csvEscape);
		}

		$count = 1;
		foreach ($this->dataSource as $row){
		    $buffer->fputcsv($this->convertEncoding($row), $this->csvDelimiter, $this->csvEnclosure, $this->csvEscape);
			if ($count % 1000 === 0) {
				if ($supportsGzip) {
				    $buffer->fflush();
					ob_flush();
				}
				flush();
			}
			$count++;
		}
		$buffer->fflush();
		$buffer = null;
	}

	private function getUtf8BomBytes(){
		return chr(239) . chr(187) . chr(191);
	}

}
