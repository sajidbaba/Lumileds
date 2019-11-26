<?php

namespace AppBundle\Services;

use AppBundle\Entity\Cell;
use AppBundle\Entity\CellError;
use AppBundle\Indicators\Input\InputIndicatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class CellValidator
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var CellError[]
     */
    private $queue;

    /**
     * CellValidator constructor.
     *
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     */
    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }

    /**
     * Validate cell
     *
     * @param Cell $cell
     * @param Cell|null $previousCell
     *
     * @return void
     */
    public function validate(Cell $cell, ?Cell $previousCell)
    {
        $indicator = $cell->getIndicatorClass();
        if (!$indicator instanceof InputIndicatorInterface) {
            return;
        }

        if (!$indicator->isValid($cell)) {
            $this->addCellError($cell, $indicator->getErrorMessage());
        } elseif (!$indicator->variationIsValid($cell, $previousCell)) {
            $this->addCellError($cell, $indicator->getVariationErrorMessage(), CellError::TYPE_WARNING);
        } elseif ($cell->getErrorLog()) {
            $this->em->remove($cell->getErrorLog());
            $cell->setErrorLog(null);
            unset($this->queue[$cell->getId()]);
        }
    }

    /**
     * Validate next cell
     *
     * @param Cell|null $nextCell
     * @param Cell $cell
     *
     * @return void
     */
    public function validateNextCell(?Cell $nextCell, Cell $cell)
    {
        $indicator = $cell->getIndicatorClass();
        if (!$indicator instanceof InputIndicatorInterface) {
            return;
        }

        if ($nextCell) {
            if (!$indicator->variationIsValid($nextCell, $cell)) {
                $this->addCellError($nextCell, $indicator->getVariationErrorMessage(), CellError::TYPE_WARNING);
            } elseif ($nextCell->getErrorLog()) {
                $this->em->remove($nextCell->getErrorLog());
                $nextCell->setErrorLog(null);
                unset($this->queue[$cell->getId()]);
            }
        }
    }

    /**
     * Add error for cell
     *
     * @param Cell $cell
     * @param string $id
     * @param int $type
     */
    public function addCellError(Cell $cell, string $id, $type = CellError::TYPE_ERROR)
    {
        $message = $this->translator->trans($id);

        if ($error = $cell->getErrorLog()) {
            $error
                ->setMessage($message)
                ->setType($type);
        } else {
            $error = (new CellError())
                ->setCell($cell)
                ->setMessage($message)
                ->setType($type);
        }

        $this->queue[$cell->getId()] = [
            'cell' => $cell,
            'error' => $error,
        ];
    }

    /**
     * Add error
     *
     * @param string $id
     * @param array $params
     */
    public function addError(string $id, $params)
    {
        $message = $this->translator->trans($id, $params);

        $error = (new CellError())
            ->setMessage($message)
            ->setType(CellError::TYPE_FILE_ERROR);

        $this->em->persist($error);
    }

    /**
     * Clear errors that are not related to cells
     */
    public function removeUploadErrors()
    {
        $this->em->getRepository(CellError::class)->removeUploadErrors();
    }

    /**
     * After the validation was triggered for a Cell, it (this same cell) might be fetched
     * again from DB as a dependency for the calculation process. This will trigger an error,
     * when trying to persist result of this query in cache (because a non flushed Cell error does not have and id).
     *
     * Flushing the CellError right after the validation for a cell is also not a solution
     * in current implementation, as it is some times triggered without actually
     * saving the results @see \AppBundle\Controller\Api\EditController::calculateAction()
     *
     * So in order to:
     *
     * 1. Avoid refactoring (given couple days for performance optimizations);
     * 2. Improve performance;
     * 3. Keep the cache intact;
     *
     * The persist part is moved to a separate method here, which should be triggered ONLY after all the fetching
     * from DB (for Cells in particular) is done.
     */
    public function persistQueuedErrors(): void
    {
        foreach ($this->queue as $pair) {
            /** @var Cell $cell */
            $cell = $pair['cell'];
            /** @var CellError $cell */
            $error = $pair['error'];

            $cell->setErrorLog($error);
            $this->em->persist($error);
        }
    }
}
