<?php

declare(strict_types=1);

namespace Models\ContentSecurityPolicy;

use App\Database\DB;
use App\Exceptions\ContentSecurityPolicyExceptions;

class CSPApprovedDomains
{
    protected DB $_db;
    public function __construct()
    {
        $this->_db = new DB();
    }
    public function domainExist(string $domain): bool
    {
        $pdo = $this->_db->getConnection();
        $query = "SELECT * FROM csp_approved_domains WHERE domain=?";
        $stmt = $pdo->prepare($query);

        try {
            $stmt->execute([$domain]);
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return count($result) > 0;
        } catch (\PDOException $e) {
            if (ERROR_VERBOSE) {
                throw (new ContentSecurityPolicyExceptions())->genericError($e->getMessage(), 500);
            } else {
                throw (new ContentSecurityPolicyExceptions())->genericError('Error checking CSP approved domain', 500);
            }
        }
    }
    public function getAll(): array
    {
        $pdo = $this->_db->getConnection();
        $query = "SELECT * FROM csp_approved_domains";
        $stmt = $pdo->prepare($query);

        try {
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            if (ERROR_VERBOSE) {
                throw (new ContentSecurityPolicyExceptions())->genericError($e->getMessage(), 500);
            } else {
                throw (new ContentSecurityPolicyExceptions())->genericError('Error fetching CSP approved domains', 500);
            }
            return [];
        }
    }
}
