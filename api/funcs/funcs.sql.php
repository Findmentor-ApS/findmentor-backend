<?php
// function getMentorsSearch($query)
// {
//     $select = "SELECT count(u.id) ";
//     $sql = "FROM users u
//     LEFT JOIN users_mentor_types umt ON umt.user_id = u.id
//     LEFT JOIN mentor_types mt ON mt.id = umt.mentor_type_id
//     LEFT JOIN mentor_geographies mg ON mg.user_id = u.id
//     LEFT JOIN communes c ON mg.commune_id = c.id
//     WHERE u.usertype = 'mentor'";

//     $vals = [];
//     if (isset($query['id'])) {
//         $sql .= " AND u.id = ?";
//         $vals[] = $query['id'];
//     }

//     if (isset($query['gender'])) {
//         $sql .= " AND u.gender = ?";
//         $vals[] = $query['gender'];
//     }

//     if (isset($query['geography'])) {
//         if (is_array($query['geography'])) {
//             $sql .= " AND (";
//             foreach ($query['geography'] as $geo) {
//                 $sql .= "mg.commune_id = ? OR ";
//                 $vals[] = $geo;
//             }
//             $sql = trim($sql, ' OR ') . ')';
//         } else {
//             $sql .= " AND mg.commune_id = ?";
//             $vals[] = $query['geography'];
//         }
//     }

//     $res = DI::database()->sql($select . $sql, $vals, 2);
//     $count = !empty($res) ? $res[0] : 0;

//     if (!isset($query['page']) || !is_numeric($query['page'])) $query['page'] = 0;
//     if (!isset($query['perpage']) || !is_numeric($query['perpage'])) $query['perpage'] = 10;

//     $select = "SELECT u.id, u.email, u.firstname, u.lastname, u.phone, GROUP_CONCAT(mt.type) AS mentor_types ";
//     $sql .= " GROUP BY u.id LIMIT " . ($query['page'] * $query['perpage']) . "," . $query['perpage'];

//     return [
//         "result" => DI::database()->sql($select . $sql, $vals, 2),
//         "totalItems" => $count
//     ];
// }

// function getMentorsSearch($query, $page, $perPage) {
//     $select = "SELECT count(u.id) AS total_count ";
//     $sql = "FROM users u
//     LEFT JOIN users_mentor_types umt ON umt.user_id = u.id
//     LEFT JOIN mentor_types mt ON mt.id = umt.mentor_type_id
//     LEFT JOIN mentor_geographies mg ON mg.user_id = u.id
//     LEFT JOIN communes c ON mg.commune_id = c.id
//     WHERE u.usertype = 'mentor'";

//     $vals = [];
//     $scoreQuery = '';
//     $scoreVals = [];

//     if (isset($query['gender'])) {
//         $sql .= " AND u.gender = ?";
//         $vals[] = $query['gender'];
//         $scoreQuery .= "(u.gender = ?) * 5 + ";
//         $scoreVals[] = $query['gender'];
//     }

//     if (isset($query['geography'])) {
//         if (is_array($query['geography'])) {
//             $sql .= " AND (";
//             foreach ($query['geography'] as $geo) {
//                 $sql .= "mg.commune_id = ? OR ";
//                 $vals[] = $geo;
//                 $scoreQuery .= "(mg.commune_id = ?) * 5 + ";
//                 $scoreVals[] = $geo;
//             }
//             $sql = trim($sql, ' OR ') . ')';
//             $scoreQuery = trim($scoreQuery, ' + ');
//         } else {
//             $sql .= " AND mg.commune_id = ?";
//             $vals[] = $query['geography'];
//             $scoreQuery .= "(mg.commune_id = ?) * 5 + ";
//             $scoreVals[] = $query['geography'];
//         }
//     }

//     if (isset($query['typeForm'])) {
//         if (is_array($query['typeForm'])) {
//             $sql .= " AND (";
//             foreach ($query['typeForm'] as $typeForm) {
//                 $sql .= "umt.mentor_type_id = ? OR ";
//                 $vals[] = $typeForm;
//                 $scoreQuery .= "(umt.mentor_type_id = ?) * 5 + ";
//                 $scoreVals[] = $typeForm;
//             }
//             $sql = trim($sql, ' OR ') . ')';
//             $scoreQuery = trim($scoreQuery, ' + ');
//         } else {
//             $sql .= " AND umt.mentor_type_id = ?";
//             $vals[] = $query['typeForm'];
//             $scoreQuery .= "(umt.mentor_type_id = ?) * 5 + ";
//             $scoreVals[] = $query['typeForm'];
//         }
//     }

//     if (isset($query['language'])) {
//         if (is_array($query['language'])) {
//             $sql .= " AND (";
//             foreach ($query['language'] as $language) {
//                 $sql .= "u.language = ? OR ";
//                 $vals[] = $language;
//                 $scoreQuery .= "(u.language = ?) * 5 + ";
//                 $scoreVals[] = $language;
//             }
//             $sql = trim($sql, ' OR ') . ')';
//             $scoreQuery = trim($scoreQuery, ' + ');
//         } else {
//             $sql .= " AND u.language = ?";
//             $vals[] = $query['language'];
//             $scoreQuery .= "(u.language = ?) * 5 + ";
//             $scoreVals[] = $query['language'];
//         }
//     }

//     if (isset($query['search'])) {
//         $searchTerm = $query['search'];
//         $sql .= " AND (u.firstname LIKE ? OR u.lastname LIKE ? OR u.email LIKE ?)";
//         $vals[] = "%$searchTerm%";
//         $vals[] = "%$searchTerm%";
//         $vals[] = "%$searchTerm%";
//         $scoreQuery .= "(u.firstname LIKE ? OR u.lastname LIKE ? OR u.email LIKE ?) * 2 + ";
//         $scoreVals[] = "%$searchTerm%";
//         $scoreVals[] = "%$searchTerm%";
//         $scoreVals[] = "%$searchTerm%";
//     }
    
//     if (isset($query['contact'])) {
//         $contactMethod = $query['contact'];
//         $sql .= " AND u.contact_method = ?";
//         $vals[] = $contactMethod;
//         $scoreQuery .= "(u.contact_method = ?) * 5 + ";
//         $scoreVals[] = $contactMethod;
//     }
    
//     if (isset($query['target'])) {
//         $targetAudience = $query['target'];
//         $sql .= " AND u.target_audience = ?";
//         $vals[] = $targetAudience;
//         $scoreQuery .= "(u.target_audience = ?) * 5 + ";
//         $scoreVals[] = $targetAudience;
//     }
    
//     $sql .= " GROUP BY u.id ORDER BY ({$scoreQuery} 0) DESC LIMIT $perPage OFFSET " . ($page * $perPage);
    
//     $mentors = R::getAll("SELECT u.id, u.email, u.firstname, u.lastname, u.phone, GROUP_CONCAT(mt.type) AS mentor_types, ($scoreQuery 0) as score " . $sql, $scoreVals);
    
//     $count = R::getCell($select . $sql, array_merge($scoreVals, $vals));
    
//     return [
//         "result" => $mentors,
//         "totalItems" => $count
//     ];
// }

function searchMentors($selectedMentorType, $selectedLocationArr, $selectedTypeForm, $selectedLanguage, $selectedGender, $selectedContact, $selectedAudience, $page, $perPage = 10) {
    $offset = ((int)$page - 1) * (int)$perPage;
    // $offset = 1 * 10;

    // Generate SQL query for your search
    $mentors = R::getAll(
        'SELECT mentor.*, 
            (
                IF(mentor.mentor_type = ?, 5, 0) +
                IF(mentor.gender IN (' . R::genSlots($selectedGender) . '), 5, 0) +
                (
                    SELECT COUNT(*) FROM language 
                    WHERE mentor.id = language.mentor_id 
                    AND language.language_type IN (' . R::genSlots($selectedLanguage) . ')
                ) * 3 +
                (
                    SELECT COUNT(*) FROM location 
                    WHERE mentor.id = location.mentor_id 
                    AND location.location_type IN (' . R::genSlots($selectedLocationArr) . ')
                ) * 3 +
                (
                    SELECT COUNT(*) FROM experience 
                    WHERE mentor.id = experience.mentor_id 
                    AND experience.experience_type IN (' . R::genSlots($selectedTypeForm) . ')
                ) * 3 +
                (
                    SELECT COUNT(*) FROM contact 
                    WHERE mentor.id = contact.mentor_id 
                    AND contact.contact_type IN (' . R::genSlots($selectedContact) . ')
                ) * 3 +
                (
                    SELECT COUNT(*) FROM audience 
                    WHERE mentor.id = audience.mentor_id 
                    AND audience.audience_type IN (' . R::genSlots($selectedAudience) . ')
                ) * 3
            ) AS score
        FROM mentor 
        WHERE mentor.is_available = 1
        GROUP BY mentor.id
        ORDER BY score DESC, mentor.id DESC
        LIMIT ? OFFSET ?',
        array_merge([$selectedMentorType], $selectedGender, $selectedLanguage, $selectedLocationArr, $selectedTypeForm, $selectedContact, $selectedAudience, [$perPage, $offset])
    );
    
    

    // Fetch the mentors
    $result = [];
    foreach ($mentors as $mentor) {
        $user = fetchUser($mentor, 'mentor');
        $user['score'] = $mentor['score'];
        // $user['profile_picture'] = 'fsadfa';
        $result[] = $user;
    }
    $totalItems = count($result);
    // Return the result
    return [
        'result' => $result,
        'totalItems' => R::getCell('SELECT COUNT(*) FROM mentor WHERE is_available = 1')
    ];
}



function fetchUser($user, $usertype) {
    if($usertype == 'mentor') {
        $user['experiences'] = R::find('experience', 'mentor_id = ?', [$user['id']]);
        $user['contacts'] = R::find('contact', 'mentor_id = ?', [$user['id']]);
        $user['languages'] = R::find('language', 'mentor_id = ?', [$user['id']]);
        $user['locations'] = R::find('location', 'mentor_id = ?', [$user['id']]);
        $user['audiences'] = R::find('audience', 'mentor_id = ?', [$user['id']]);
    }
    // if commune do not unset phone
    else if ($user['usertype'] == 'user') {
        unset($user['phone']);
    }
    unset($user['access_token'], $user['created'],
        $user['post_code'],$user['street'],$user['street_no'],$user['street_side'],
        $user['login_token'],$user['verify_email_token']);
    return $user;
}

function fetchProfile($user, $usertype) {
    if($usertype == 'mentor') {
        $user['experiences'] = R::find('experience', 'mentor_id = ?', [$user['id']]);
        $user['contacts'] = R::find('contact', 'mentor_id = ?', [$user['id']]);
        $user['languages'] = R::find('language', 'mentor_id = ?', [$user['id']]);
        $user['locations'] = R::find('location', 'mentor_id = ?', [$user['id']]);
        $user['audiences'] = R::find('audience', 'mentor_id = ?', [$user['id']]);


        $start_of_week = new DateTime('this week');
        $end_of_week = new DateTime('next week');
        //
        $user['bookingsTotal'] = R::count('booking', 'mentor_id = ?', [$user['id']]);
        $user['callsTotal'] = R::count('call', 'mentor_id = ?', [$user['id']]);
        $user['visitsTotal'] = R::count('visit', 'mentor_id = ?', [$user['id']]);
        $user['bookingsTotalWeek'] = R::count('booking', 'mentor_id = ? AND created_at >= ? AND created_at < ?', [$user['id'], $start_of_week->format('Y-m-d'), $end_of_week->format('Y-m-d')]);
        $user['callsTotalWeek'] = R::count('call', 'mentor_id = ? AND created_at >= ? AND created_at < ?', [$user['id'], $start_of_week->format('Y-m-d'), $end_of_week->format('Y-m-d')]);
        $user['visitsTotalWeek'] = R::count('visit', 'mentor_id = ? AND created_at >= ? AND created_at < ?', [$user['id'], $start_of_week->format('Y-m-d'), $end_of_week->format('Y-m-d')]);
    }
    return $user;
}