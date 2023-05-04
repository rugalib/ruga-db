<?php

declare(strict_types=1);

namespace Ruga\Db\Row\Feature;


/**
 * The transaction feature wraps all save operation into a transaction.
 */
class TransactionFeature extends AbstractFeature
{
    private ?\Throwable $saveException = null;
    
    /**
     * @var bool Stores previous transaction state. Used to determine if a new transaction should be started and if
     *           roll back or commit is to be executed.
     */
    private bool $inTransaction = false;
    
    
    
    /**
     * Start transaction before saving.
     *
     * @return void
     */
    public function startSave()
    {
        // Save the current state of transaction...
        $this->inTransaction = $this->rowGateway->sql->getAdapter()->getDriver()->getConnection()->inTransaction();
        if (!$this->inTransaction) {
            // ...and start new transaction, if not already startet
            \Ruga\Log::addLog(
                "Transaction started",
                \Ruga\Log\Severity::INFORMATIONAL,
                \Ruga\Log\Type::RESULT,
                $this->rowGateway,
            );
            $this->rowGateway->sql->getAdapter()->getDriver()->getConnection()->beginTransaction();
        }
    }
    
    
    
    public function catchSaveException(\Throwable $exception): void
    {
        $this->saveException = $exception;
    }
    
    
    
    public function endSave()
    {
        if ($this->saveException) {
            // command was NOT successful => roll back if transaction was startet by this object
            if (!$this->inTransaction) {
                \Ruga\Log::addLog(
                    "Transaction rolled back",
                    \Ruga\Log\Severity::WARNING,
                    \Ruga\Log\Type::RESULT,
                    $this->rowGateway
                );
                $this->rowGateway->sql->getAdapter()->getDriver()->getConnection()->rollback();
            }
        } else {
            // command was successful => commit if transaction was startet by this object
            if (!$this->inTransaction) {
                \Ruga\Log::addLog(
                    "Transaction commited",
                    \Ruga\Log\Severity::NOTICE,
                    \Ruga\Log\Type::RESULT,
                    $this->rowGateway
                );
                $this->rowGateway->sql->getAdapter()->getDriver()->getConnection()->commit();
            }
        }
    }
    
    
}