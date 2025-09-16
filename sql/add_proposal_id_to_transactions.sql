ALTER TABLE financial_transactions
ADD COLUMN proposal_id INT NULL,
ADD FOREIGN KEY (proposal_id) REFERENCES proposals(id) ON DELETE SET NULL;