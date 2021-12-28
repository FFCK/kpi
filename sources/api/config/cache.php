<?php

/**
 * Renvoie la data du fichier json s'il a moins de $cache_duration minutes d'ancienneté
 */
function json_cache_read($cache_type, $cache_id, $cache_duration = 5)
{
  $cache_id = $cache_id !== false ? '_' . $cache_id : '';
  $file_name = 'files/' . $cache_type . $cache_id . '.json';
  try {
    $file_content = json_decode(file_get_contents($file_name), false);
  } catch (Exception $e) {
    return false;
  }
  if ($file_content) {
    $file_date = $file_content->created_at;
    $file_date = DateTime::createFromFormat('Y-m-d H:i:s', $file_date);
    $file_date->add(new DateInterval('PT' . $cache_duration . 'M'));
    $now = new DateTime("now");
    if ($now <= $file_date) {
      return $file_content->data;
    }
  }

  return false;
}

/**
 * Ecrit la data et la date de création dans un fichier json
 */
function json_cache_write($cache_type, $cache_id, $cache_content)
{
  $cache_id = $cache_id !== false ? '_' . $cache_id : '';
  $file_name = 'files/' . $cache_type . $cache_id . '.json';
  $file_content = json_encode([
    'created_at' => date('Y-m-d H:i:s'),
    'data' => $cache_content
  ]);
  file_put_contents($file_name, $file_content);
}
