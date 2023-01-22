<?php
function getMentorsSearch($query)
{
    $select = "SELECT count(u.id) ";
    $sql = "FROM users u
    LEFT JOIN users_mentor_types umt ON umt.user_id = u.id
    LEFT JOIN mentor_types mt ON mt.id = umt.mentor_type_id
    LEFT JOIN mentor_geographies mg ON mg.user_id = u.id
    LEFT JOIN communes c ON mg.commune_id = c.id
    WHERE u.usertype = 'mentor'";

    $vals = [];
    if (isset($query['id'])) {
        $sql .= " AND u.id = ?";
        $vals[] = $query['id'];
    }

    if (isset($query['gender'])) {
        $sql .= " AND u.gender = ?";
        $vals[] = $query['gender'];
    }

    if (isset($query['geography'])) {
        if (is_array($query['geography'])) {
            $sql .= " AND (";
            foreach ($query['geography'] as $geo) {
                $sql .= "mg.commune_id = ? OR ";
                $vals[] = $geo;
            }
            $sql = trim($sql, ' OR ') . ')';
        } else {
            $sql .= " AND mg.commune_id = ?";
            $vals[] = $query['geography'];
        }
    }

    $res = DI::database()->sql($select . $sql, $vals, 2);
    $count = !empty($res) ? $res[0] : 0;

    if (!isset($query['page']) || !is_numeric($query['page'])) $query['page'] = 0;
    if (!isset($query['perpage']) || !is_numeric($query['perpage'])) $query['perpage'] = 10;

    $select = "SELECT u.id, u.email, u.firstname, u.lastname, u.phone, GROUP_CONCAT(mt.type) AS mentor_types ";
    $sql .= " GROUP BY u.id LIMIT " . ($query['page'] * $query['perpage']) . "," . $query['perpage'];

    return [
        "result" => DI::database()->sql($select . $sql, $vals, 2),
        "totalItems" => $count
    ];
}
