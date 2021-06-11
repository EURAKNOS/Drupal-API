<?php

namespace Drupal\eureka_dspace\Plugin\migrate\process;

use Drupal\Core\File\FileSystemInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\ProcessPluginBase;

/**
 * Files in DSpace are referenced through bundles which are referenced through bitstreams.
 * This ProcessPlugin copies those files and creates them locally.
 *
 * @MigrateProcessPlugin(
 *   id = "dspace_document"
 * )
 */
class DspaceDocument extends ProcessPluginBase {

  const FILE_DIRECTORY = 'public://dspace-documents/';

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Renaming value to endpoint to have it make more sense here.
    $endpoint = $value;
    $value = NULL;

    $filetype = $row->getSourceProperty('filetype');

    // Other filetypes don't actually have files (e.g. Videos are links to youtube, vimeo, ...).
    // So we only use this process for documents. Otherwise this field should be empty.
    if ($filetype === 'document') {
      if (is_array($endpoint)) {
        reset($endpoint);
      }

      $token = \Drupal::state()->get('dspace.token');

      // Getting the bundles.
      $response = \Drupal::httpClient()->request(
        'GET',
        $endpoint,
        [
          'headers' => ['Authorization' => $token],
        ]
      );

      $body = $response->getBody();
      $data = json_decode($body, TRUE);

      $bundles = $data['_embedded']['bundles'];

      $bundle = reset($bundles);

      // Getting the bitstreams.
      $bitstream_endpoint = $bundle['_links']['bitstreams']['href'];
      if ($bitstream_endpoint) {
        $response = \Drupal::httpClient()->request(
          'GET',
          $bitstream_endpoint,
          [
            'headers' => ['Authorization' => $token],
          ]
        );

        $body = $response->getBody();
        $data = json_decode($body, TRUE);
        $bitstreams = $data['_embedded']['bitstreams'];

        // Creating the DSpace file locally.
        if ($bitstreams) {
          $bitstream = reset($bitstreams);

          $file_url = $bitstream['_links']['content']['href'];

          if ($file_url) {
            $file_contents = file_get_contents($file_url);

            $dir = self::FILE_DIRECTORY;
            \Drupal::service('file_system')->prepareDirectory($dir, FileSystemInterface::CREATE_DIRECTORY);
            $file = file_save_data($file_contents, self::FILE_DIRECTORY . urldecode($bitstream['name']));
            if ($file) {
              return $file;
            }
          }
        }
      }
    }

    return $value;
  }

}
