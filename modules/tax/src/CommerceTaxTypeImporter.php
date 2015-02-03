<?php

/**
 * @file
 * Contains \Drupal\commerce_tax\CommerceTaxTypeImporter.
 */

namespace Drupal\commerce_tax;

use \CommerceGuys\Tax\Repository\TaxTypeRepository;
use \Drupal\Core\Entity\EntityManagerInterface;
use \Drupal\Core\Entity\EntityStorageInterface;
use \CommerceGuys\Tax\Model\TaxTypeInterface;
use \CommerceGuys\Tax\Model\TaxRateInterface;
use \CommerceGuys\Tax\Model\TaxRateAmountInterface;
use \Drupal\Core\StringTranslation\StringTranslationTrait;
use \Drupal\Core\StringTranslation\TranslationInterface;

class CommerceTaxTypeImporter implements CommerceTaxTypeImporterInterface {

  use StringTranslationTrait;

  /**
   * The tax type storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $taxTypeStorage;

  /**
   * The tax rate storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $taxRateStorage;

  /**
   * The tax rate amount storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $taxRateAmountStorage;

  /**
   * The tax type repository.
   *
   * @var \CommerceGuys\Tax\Repository\TaxTypeRepositoryInterface
   */
  protected $taxTypeRepository;

  /**
   * Constructs a new CommerceTaxTypeImporter.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   *   The entity manager.
   * @param string
   *   The tax types folder of definitions.
   */
  public function __construct(EntityManagerInterface $entityManager, TranslationInterface $stringTranslation, $taxTypesFolder = NULL) {
    $this->taxTypeStorage = $entityManager->getStorage('commerce_tax_type');
    $this->taxRateStorage = $entityManager->getStorage('commerce_tax_rate');
    $this->taxRateAmountStorage = $entityManager->getStorage('commerce_tax_rate_amount');
    $this->stringTranslation = $stringTranslation;
    $this->taxTypeRepository = new TaxTypeRepository($taxTypesFolder);
  }

  /**
   * {@inheritdoc}
   */
  public function getImportableTaxTypes() {
    $importableTaxTypes = $this->taxTypeRepository->getAll();
    $importedTaxTypes = $this->taxTypeStorage->loadMultiple();

    // Remove any already imported tax types.
    foreach ($importedTaxTypes as $taxType) {
      if (isset($importableTaxTypes[$taxType->getId()])) {
        unset($importableTaxTypes[$taxType->getId()]);
      }
    }

    return $importableTaxTypes;
  }

  /**
   * {@inheritdoc}
   */
  public function createTaxType($taxTypeId) {
    $taxType = $this->taxTypeRepository->get($taxTypeId);
    return $this->importTaxType($taxType);
  }

  /**
   * Imports a single tax type.
   *
   * @param \CommerceGuys\Tax\Model\TaxTypeInterface $taxType
   *   The tax type.
   */
  protected function importTaxType(TaxTypeInterface $taxType) {
    if ($this->taxTypeStorage->load($taxType->getId())) {
      return;
    }

    $values = array(
      'id' => $taxType->getId(),
      'name' => $this->t($taxType->getName()),
      'compound' => $taxType->isCompound(),
      'roundingMode' => $taxType->getRoundingMode(),
      'tag' => $taxType->getTag(),
      'rates' => array_keys($taxType->getRates()),
    );

    return $this->taxTypeStorage->create($values);
  }

  /**
   * Imports a single tax rate.
   *
   * @param \CommerceGuys\Tax\Model\TaxRateInterface $taxRate
   *   The tax rate to import.
   */
  public function importTaxRate(TaxRateInterface $taxRate) {
    $values = array(
      'type' => $taxRate->getType()->getId(),
      'id' => $taxRate->getId(),
      'name' => $this->t($taxRate->getName()),
      'displayName' => $this->t($taxRate->getDisplayName()),
      'default' => $taxRate->isDefault(),
      'amounts' => array_keys($taxRate->getAmounts()),
    );

    return $this->taxRateStorage->create($values);
  }

  /**
   * Imports a single tax rate amount.
   *
   * @param \CommerceGuys\Tax\Model\TaxRateAmountInterface $taxRateAmount
   *   The tax rate amount to import.
   */
  protected function importTaxRateAmount(TaxRateAmountInterface $taxRateAmount) {
    $startDate = $taxRateAmount->getStartDate() ? $taxRateAmount->getStartDate()->getTimestamp() : NULL;
    $endDate = $taxRateAmount->getEndDate() ? $taxRateAmount->getEndDate()->getTimestamp() : NULL;
    $values = array(
      'rate' => $taxRateAmount->getRate()->getId(),
      'id' => $taxRateAmount->getId(),
      'amount' => $taxRateAmount->getAmount(),
      'startDate' => $startDate,
      'endDate' => $endDate,
    );

    return $this->taxRateAmountStorage->create($values);
  }

}