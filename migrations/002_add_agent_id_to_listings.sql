ALTER TABLE `listings`
  ADD COLUMN `agent_id` INT NULL AFTER `featured`,
  ADD INDEX (`agent_id`);
