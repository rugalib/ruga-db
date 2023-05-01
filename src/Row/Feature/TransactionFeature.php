<?php

declare(strict_types=1);

namespace Ruga\Db\Row\Feature;


/**
 * The transaction feature wraps all save operation into a transaction.
 */
class TransactionFeature extends AbstractFeature
{
    private ?\Throwable $saveException=null;
    /**
     * Start transaction before saving.
     *
     * @return void
     */
    public function startSave()
    {
        \Ruga\Log::addLog("Transaction started", \Ruga\Log\Severity::INFORMATIONAL, \Ruga\Log\Type::RESULT);
        $this->rowGateway->sql->getAdapter()->getDriver()->getConnection()->beginTransaction();
    }
    
    public function catchSaveException(\Throwable $exception): void
    {
        $this->saveException=$exception;
    }
    
    
    
    public function endSave()
    {
        if($this->saveException) {
            // command was NOT successful
            $this->rowGateway->sql->getAdapter()->getDriver()->getConnection()->rollback();
            \Ruga\Log::addLog("Transaction rolled back", \Ruga\Log\Severity::WARNING, \Ruga\Log\Type::RESULT);
        } else {
            // command was successful
            $this->rowGateway->sql->getAdapter()->getDriver()->getConnection()->commit();
            \Ruga\Log::addLog("Transaction commited", \Ruga\Log\Severity::NOTICE, \Ruga\Log\Type::RESULT);
        }
    }
    
    
}