<?php

namespace Drupal\nrfc_import_fixtures\Drush\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use DateTime;
use DateTimeImmutable;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Utility\Token;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Plugin\views\argument\Taxonomy;
use Drush\Attributes as CLI;
use Drush\Attributes\Command;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;
use Exception;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * A Drush commandfile.
 * ddev drush entity:delete node --bundle=fixture
 * ddev drush nifi /var/www/html/web/fixtures.csv
 */
final class NrfcImportFixturesCommands extends DrushCommands
{

  use AutowireTrait;

  private \Drupal\Core\Entity\EntityStorageInterface $term_storage;
  private \Drupal\Core\Entity\EntityStorageInterface $node_storage;

  /**
   * Constructs a NrfcImportFixturesCommands object.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  )
  {
    parent::__construct();
    $this->term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $this->node_storage = \Drupal::entityTypeManager()->getStorage('node');
  }

  /**
   * Command description here.
   */
  #[CLI\Command(name: 'nrfc_import_fixtures:import', aliases: ['nifi'])]
  #[CLI\Argument(name: 'filename', description: 'CSV File to import.')]
  #[CLI\Usage(name: 'nrfc_import_fixtures:import filename.csv', description: 'Bulk import fixtures from CSV file.')]
  public function import($filename, $clearAll = false): void
  {
    if ($clearAll) {
      $this->delete_all_fixtures();;
    }
    $this->logger()->success(dt('Achievement unlocked.'));

    if (!file_exists($filename)) {
      throw new FileNotFoundException(sprintf('File "%s" not found', $filename));
    }
    $file = fopen($filename, 'r');
    if ($file === false) {
      $this->logger->error('Could not open the file');
      return;
    }

    // skip headers
    fgetcsv($file, 0);

    $importedCount = 0;
    $rowNumber = 0;
    try {
      while (($row = fgetcsv($file, 0)) !== false) {
        $rowNumber++;

        try {
          // Skip empty rows
          if (empty(array_filter($row))) {
            continue;
          }

          $this->processRow($row);

          $importedCount++;
        } catch (Exception $e) {
          $this->logger->warning(sprintf(
            'Error processing row %d: %s. Row data: %s',
            $rowNumber,
            $e->getMessage(),
            implode(',', $row)
          ));
          continue;
        }
      }
    } catch (Exception $e) {
      $this->logger->error(sprintf('Import failed: %s', $e->getMessage()));
    }
  }


  /**
   * Creates an entity from a CSV row
   *
   * @param array $row
   * @throws Exception When row data is invalid
   */
  private function processRow(array $row): void
  {
    $date = DateTimeImmutable::createFromMutable(
      DateTime::createFromFormat('j-M-y', $row[0])
    )->setTime(0, 1, 0);

    $this->createFixture($this->load_term_by_name('Minis'), $date, $row[2]);
    $this->createFixture($this->load_term_by_name('u13B'), $date, $row[3]);
    $this->createFixture($this->load_term_by_name('u14B'), $date, $row[4]);
    $this->createFixture($this->load_term_by_name('u15B'), $date, $row[5]);
    $this->createFixture($this->load_term_by_name('u16B'), $date, $row[6]);
    $this->createFixture($this->load_term_by_name('Colts'), $date, $row[7]);
    $this->createFixture($this->load_term_by_name('u12G'), $date, $row[9]);
    $this->createFixture($this->load_term_by_name('u14G'), $date, $row[10]);
    $this->createFixture($this->load_term_by_name('u16G'), $date, $row[11]);
    $this->createFixture($this->load_term_by_name('u18G'), $date, $row[12]);
  }

  private function load_term_by_name($term_name, $vocab = 'team')
  {
    $terms = $this->term_storage->loadByProperties([
      'name' => $term_name,
      'vid' => $vocab,
    ]);

    return !empty($terms) ? reset($terms) : NULL;
  }

  /**
   * @throws EntityStorageException
   */
  private function createFixture(Term $team, DateTimeImmutable $date, mixed $detail): void
  {
    list($sessionName, $comp, $home, $club) = $this->parseDetail($detail);
    $fields = [
      'type' => 'fixture',
      'title' => $sessionName ?: "Training",
      'status' => 1,
      'field_date' => $date->format('Y-m-d'),
      'field_home_or_away' => $home,
      'field_team' => $team,
    ];
    if ($club != null) {
      $fields['field_club'] = $club;
    }
    if ($comp != null) {
      $fields['field_competition'] = $comp;
    }
    $node = Node::create($fields);
    $node->save();
  }

  /**
   * @throws EntityStorageException
   */
  private function parseDetail(string $detail): array
  {
    // We should really cache the terms for competition and home/away but this run once per year
    if (in_array(strtolower(trim($detail)), ['training', 'skills session'])) {
      return ["Training", $this->load_term_by_name('None', 'competition'), $this->load_term_by_name('Home'), null];
    }

    // is CB or Pathway
    if (
      str_starts_with(trim($detail), "CB")
      || str_contains(strtolower(trim($detail)), "pathway")
      || str_contains(strtolower(trim($detail)), "academy")
    ) {
      return [ucwords($detail), $this->load_term_by_name('Pathway', 'competition'), $this->load_term_by_name('TBA'), null];
    }
    // is county cup / colts cup
    if (
      str_starts_with(strtolower(trim($detail)), "county cup")
      || str_contains(strtolower(trim($detail)), "colts cup")
      || str_contains(strtolower(trim($detail)), "norfolk finals")
    ) {
      return [ucwords($detail), $this->load_term_by_name('CountyCup', 'competition'), $this->load_term_by_name('TBA'), null];
    }
    // is festival
    if (str_contains(strtolower(trim($detail)), "festival")) {
      return [ucwords($detail), $this->load_term_by_name('Festival', 'competition'), $this->load_term_by_name('TBA'), null];
    }
    // is nat cup
    if (str_contains(strtolower(trim($detail)), "nat cup")) {
      return [ucwords($detail), $this->load_term_by_name('NationalCup', 'competition'), $this->load_term_by_name('TBA'), null];
    }
    // is norfolk 10s
    if (str_contains(trim($detail), "Norfolk10s")) {
      return [$detail, $this->load_term_by_name('Norfolk10s', 'competition'), $this->load_term_by_name('TBA'), null];
    }
    // is Conference
    if (str_contains(strtolower(trim($detail)), "conference")) {
      return [ucwords($detail), $this->load_term_by_name('Conference', 'competition'), $this->load_term_by_name('TBA'), null];
    }
    // is special day
    if (in_array(strtolower(trim($detail)), ["mothering sunday", "christmas", "easter", "out of season"])) {
      return [ucwords($detail), $this->load_term_by_name('None', 'competition'), $this->load_term_by_name('TBA'), null];
    }

    // we've got this far, we think it's a club game
    $club = $this->findClub(preg_replace('/\s*\([^)]*\)/', '', $detail));
    return [
      ucwords($detail),
      $this->load_term_by_name('Friendly', 'competition'),
      $this->isHomeOrAway($detail),
      $club
    ];
  }

  private function isHomeOrAway($detail): Term
  {
    if (str_contains($detail, '(H)')) {
      return $this->load_term_by_name('Home', 'homeaway');
    }
    if (str_contains($detail, '(A)')) {
      return $this->load_term_by_name('Away', 'homeaway');
    }
    return $this->load_term_by_name('TBA', 'homeaway');
  }

  /**
   * @throws EntityStorageException
   */
  private function findClub($name)
  {
    $n = ucwords(trim(strtolower($name)));
    if (empty($n)) {
      return null;
    }

    switch ($n) {
      case "W Norfolk":
        $n = "West Norfolk";
        break;
      case "N Walsham":
        $n = "North Walsham";
        break;
    }

    $nodes = $this->node_storage->loadByProperties([
      'title' => $n,
      'type' => 'club',
    ]);

    if (!empty($nodes)) {
      return reset($nodes);
    }

    $node = Node::create(
      [
        'type' => 'club',
        'title' => $n,
        'status' => 1,
      ]
    );
    $node->save();

    return $node;
  }

  function delete_all_fixtures(): void
  {
    $query = \Drupal::entityQuery('node')
      ->accessCheck(FALSE)
      ->condition('type', 'fixture');
    $nids = $query->execute();

    if (empty($nids)) {
      \Drupal::messenger()->addMessage(t('No fixture nodes found to delete.'));
      return;
    }

    $storage_handler = \Drupal::entityTypeManager()->getStorage('node');
    $nodes = $storage_handler->loadMultiple($nids);
    $storage_handler->delete($nodes);

    \Drupal::messenger()->addMessage(t('Deleted @count fixture nodes.', ['@count' => count($nids)]));
  }
}
