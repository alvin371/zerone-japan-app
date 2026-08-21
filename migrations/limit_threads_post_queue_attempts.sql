-- Existing Threads post jobs may have been created with the legacy limit of
-- ten attempts. Clamp them to the current two-attempt policy.
UPDATE scraping_queue
SET max_attempts = 2
WHERE entity_type = 'endorse'
  AND scraper = 'threadsPost'
  AND max_attempts > 2;
