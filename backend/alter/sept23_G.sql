-- add human-readable label and numeric score
ALTER TABLE `feedback`
  ADD COLUMN `sentiment` VARCHAR(12)    NULL AFTER `comments`,
  ADD COLUMN `sentiment_score` FLOAT    NULL AFTER `sentiment`,
  ADD COLUMN `sentiment_model` VARCHAR(100) NULL AFTER `sentiment_score`,
  ADD COLUMN `sentiment_analyzed_at` TIMESTAMP NULL DEFAULT NULL AFTER `sentiment_model`;

---------------------------------------------------------

ALTER TABLE inbox
  ADD COLUMN sentiment VARCHAR(10) NULL AFTER message,
  ADD COLUMN sentiment_score FLOAT NULL AFTER sentiment;


