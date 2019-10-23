<?php

namespace Drupal\commerce_price\Comparator;

use Drupal\commerce_price\Price;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Comparator\ComparisonFailure;

/**
 * Provides a Comparator for price objects.
 */
class PriceComparator extends Comparator {

  /**
   * {@inheritdoc}
   */
  public function accepts($expected, $actual) {
    return $expected instanceof Price && $actual instanceof Price;
  }

  /**
   * {@inheritdoc}
   */
  public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = FALSE, $ignoreCase = FALSE) {
    if (!$actual->equals($expected)) {
      throw new ComparisonFailure(
        $expected,
        $actual,
        (string) $expected,
        (string) $actual,
        FALSE,
        \sprintf(
          'Failed asserting that Price %s matches expected %s.',
          $actual,
          $expected
        )
      );
    }
  }

}
