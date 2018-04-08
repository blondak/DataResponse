<?php

namespace Blondak\DataResponse;

use Exception;
use InvalidArgumentException;
use Nette\Application\IResponse as NetteAppIReseponse;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Application\BadRequestException;
use Nette\SmartObject;


class XlsxResponse implements NetteAppIReseponse
{
    use SmartObject;
    
    private $dataSource;
    
    /**
     * @var array
     */
    private $header = [];
    
    /**
     * @var string
     */
    private $outputFilename;
    
    private $tempDir = null;
    
    public function __construct(
        $dataSource,
        $outputFilename = 'output.xlsx',
        $tempDir = null
    ) {
        if (!(is_array($dataSource) || $dataSource instanceof \Traversable)){
            throw new BadRequestException(__METHOD__);
        }
        $this->dataSource = $dataSource;
        $this->outputFilename = $outputFilename;
        if ($tempDir){
            $this->tempDir = $tempDir;
        } else {
            $this->tempDir = sys_get_temp_dir();
        }
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
    
    public function send(IRequest $httpRequest, IResponse $httpResponse){
        $httpResponse->setContentType('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $httpResponse->setHeader('Content-Disposition', 'attachment; filename="' . $this->outputFilename . '"');
        
        $writer = new \XLSXWriter();
        $writer->setTempDir($this->tempDir);
        if (count($this->header)) {
            $writer->writeSheetRow('order', $row);
        }
        foreach ($this->dataSource as $row){
            $writer->writeSheetRow('order', $row);
        }
        
        $writer->writeToStdOut();
    }
    
    private function getUtf8BomBytes(){
        return chr(239) . chr(187) . chr(191);
    }
    
}
