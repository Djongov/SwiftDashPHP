<?php

declare(strict_types=1);

namespace Models\ContentSecurityPolicy;

use App\Database\DB;

class CSPPolicies
{
    public string $dbTable;
    private DB $db;

    public function __construct()
    {
        $this->dbTable = 'csp_policies';
    }
    public function addPolicy($policy, $description): bool
    {
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("INSERT INTO csp_policies (policy, description) VALUES (?, ?)");
        $stmt->execute([$policy, $description]);
        return ($stmt->rowCount() > 0) ? true : false;
    }
    public function deletePolicy($id): bool
    {
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("DELETE FROM csp_policies WHERE id=?");
        $stmt->execute([$id]);
        return ($stmt->rowCount() > 0) ? true : false;
    }
    public function getPolicies(): array
    {
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM csp_policies");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function getPolicyById($id): array
    {
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM csp_policies WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    public function getPolicyByDomain($domain): array
    {
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM csp_policies WHERE domain=?");
        $stmt->execute([$domain]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    public function updatePolicy($id, $policy, $description): bool
    {
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("UPDATE csp_policies SET policy=?, description=? WHERE id=?");
        $stmt->execute([$policy, $description, $id]);
        return ($stmt->rowCount() > 0) ? true : false;
    }
    public function addApprovedDomain($domain): bool
    {
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("INSERT INTO csp_approved_domains (domain) VALUES (?)");
        $stmt->execute([$domain]);
        return ($stmt->rowCount() > 0) ? true : false;
    }
    public function deleteApprovedDomain($id): bool
    {
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("DELETE FROM csp_approved_domains WHERE id=?");
        $stmt->execute([$id]);
        return ($stmt->rowCount() > 0) ? true : false;
    }
}
