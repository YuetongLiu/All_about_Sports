<?php
    session_start();
?>

<?php
    if (session_id() == "") {
        session_start();
        if (!isset($_SESSION["loggedin"])) {
            $_SESSION["loggedin"] = false;
        }
        if (!isset($_SESSION["isAdmin"])) {
            $_SESSION["isAdmin"] = 0;
        }
        if (!isset($_SESSION["id"])) {
            $_SESSION["id"] = null;
        }
    }
    global $i;
    $i = 1;
    $success = True; //keep track of errors so it redirects the page only if there are no errors
    $db_conn = OCILogon("ora_c1l1b", "a44670164", "dbhost.ugrad.cs.ubc.ca:1522/ug");

    function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
        global $db_conn, $success;
        $statement = OCIParse($db_conn, $cmdstr); //There is a set of comments at the end of the file that describe some of the OCI specific functions and how they work
    
        if (!$statement) {
            echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
            $e = OCI_Error($db_conn); // For OCIParse errors pass the       
            // connection handle
            echo htmlentities($e['message']);
            $success = False;
        }
    
        $r = OCIExecute($statement, OCI_DEFAULT);
        if (!$r) {
            // echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
            $e = oci_error($statement); // For OCIExecute errors pass the statementhandle
            if (strpos($e['message'], "unique") !== false) {
                $_SESSION['id_already_taken'] = true;
            }
            $success = False;
        } else {
    
        }
        return $statement;
    
    }
    
    function executeBoundSQL($cmdstr, $list) {
        /* Sometimes the same statement will be executed for several times ... only
         the value of variables need to be changed.
         In this case, you don't need to create the statement several times; 
         using bind variables can make the statement be shared and just parsed once.
         This is also very useful in protecting against SQL injection.  
          See the sample code below for how this functions is used */
    
        global $db_conn, $success;
        $statement = OCIParse($db_conn, $cmdstr);
    
        if (!$statement) {
            echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
            $e = OCI_Error($db_conn);
            echo htmlentities($e['message']);
            $success = False;
        }
    
        foreach ($list as $tuple) {
            foreach ($tuple as $bind => $val) {
                //echo $val;
                //echo "<br>".$bind."<br>";
                OCIBindByName($statement, $bind, $val);
                unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
    
            }
            $r = OCIExecute($statement, OCI_DEFAULT);
            if (!$r) {
                // echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
                $e = OCI_Error($statement); // For OCIExecute errors pass the statement handle
                if (array_key_exists("signup", $_POST) && strpos($e['message'], "unique") !== false) {
                    $_SESSION['id_already_taken'] = true;
                    $_SESSION["signup_success"] = false;
                }
                echo "<br>";
                $success = False;
            }
        }
    
    }
    
    function printResult($result) { //prints results from a select statement
        echo "<br>Got data from table arenas:<br>";
        echo "<table>";
        echo "<tr><th>Arena Name</th><th>Location</th><th>Capacity</th></tr>";
    
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<tr><td>" . $row["ARENA_NAME"] . "</td><td>" . $row["LOCATION"] . "</td><td>" . $row["CAPACITY"] . "</td></tr>"; //or just use "echo $row[0]" 
        }
        echo "</table>";
    
    }
    
    // Connect Oracle...
    if ($db_conn) {
    
        executePlainSQL("alter session set NLS_TIMESTAMP_FORMAT='yyyy-mm-dd hh24:mi:ss:ff'");

        if (array_key_exists('reset', $_POST)) {
            // Drop old table...
            echo "<br> dropping table <br>";
            executePlainSQL("Drop table arenas");
    
            // Create new table...
            echo "<br> creating new table <br>";
            executePlainSQL("create table arenas (arena_name varchar2(50), location varchar2(30), capacity integer, primary key (arena_name))");
            OCICommit($db_conn);
    
        } else
            if (array_key_exists('login', $_POST)) {
                $input_id = $_POST['loginID'];
                
                if ($input_id == "") {
                    $_SESSION["id_does_not_exist"] = true;
                } else {
                    $result = executePlainSQL("select * from users where users_id=" . $input_id);
                    while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                        if ($row != null) {
                            $_SESSION["id"] = $row["USERS_ID"];
                            if ($row["ISADMIN"] == 1) {
                                $_SESSION["isAdmin"] = 1;
                            } else {
                                $_SESSION["isAdmin"] = 0;
                            }
                            $_SESSION["loggedin"] = true;
                        } else {
                            $_SESSION["id_does_not_exist"] = true;
                            $_SESSION["loggedin"] = false;
                        }
                    }
                }
            } elseif (array_key_exists('signup', $_POST)) {
                if ($_POST['signupID'] == "") {
                    $_SESSION["id_is_empty"] = true;
                } else {
                    $tuple = array (
                        ":bind1" => $_POST['signupID']
                    );
                    $alltuples = array (
                        $tuple
                    );
                    $_SESSION["signup_success"] = true;
                    executeBoundSQL("insert into users values (:bind1, 0)", $alltuples);
                    OCICommit($db_conn);
                }
            } elseif (array_key_exists('arenainsertsubmit', $_POST)) {
                //Getting the values from user and insert data into the table
                $tuple = array (
                    ":bind1" => $_POST['insAN'],
                    ":bind2" => $_POST['insLocation'],
                    ":bind3" => $_POST['insCapacity']
                );
                $alltuples = array (
                    $tuple
                );
                executeBoundSQL("insert into arenas values (:bind1, :bind2, :bind3)", $alltuples);
                OCICommit($db_conn);
    
            } elseif (array_key_exists('leagueinsertsubmit', $_POST)) {
                $tuple = array (
                    ":bind1" => $_POST['insLN']
                );
                $alltuples = array (
                    $tuple
                );
                executeBoundSQL("insert into leagues values (:bind1)", $alltuples);
                OCICommit($db_conn);
            } elseif (array_key_exists('matchinsertsubmit', $_POST)) {
                $tuple = array (
                    ":bind1" => $_POST['insMatchD'],
                    ":bind2" => $_POST['insMatchTAN'],
                    ":bind3" => $_POST['insMatchTBN'],
                    ":bind4" => $_POST['insMatchAN'],
                    ":bind5" => $_POST['insMatchTP'],
                    ":bind6" => $_POST['insMatchLN']
                );
                $alltuples = array (
                    $tuple
                );
                executeBoundSQL("insert into matches values (:bind1, :bind2, null, :bind3, null, :bind4, :bind5, 0, :bind6)", $alltuples);
                OCICommit($db_conn);
            } elseif (array_key_exists('playerinsertsubmit', $_POST)) {
                $tuple = array (
                    ":bind1" => $_POST['inaPlayerID'],
                    ":bind2" => $_POST['insPlayerDoB'],
                    ":bind3" => $_POST['insPlayerP'],
                    ":bind4" => $_POST['insPlayerN'],
                    ":bind5" => $_POST['insPlayerContactNo'],
                    ":bind6" => $_POST['insPlayerTN'],
                    ":bind7" => $_POST['insPlayerLN']
                );
                $alltuples = array (
                    $tuple
                );
                executeBoundSQL("insert into playerSignContract values (:bind1, :bind2, :bind3, :bind4, :bind5, :bind6, :bind7)", $alltuples);
                OCICommit($db_conn);
            } elseif (array_key_exists('subscribePLsubmit', $_POST)) {
                $_SESSION["id"] = $_POST['subscribeID'];
                $_SESSION["loggedin"] = true;
                $result = executePlainSQL("select player_id from playerSignContract where name ='" . $_POST['subscribePL'] . "'");
                $player_id = null;
                while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                    $player_id = $row["PLAYER_ID"];
                }
                executePlainSQL("insert into usersSubscribePlayer values (" . $_POST['subscribeID'] . ", " . $player_id . ")");
                OCICommit($db_conn);
            } elseif (array_key_exists('subscribeTMsubmit', $_POST)) {
                $_SESSION["id"] = $_POST['subscribeID'];
                $_SESSION["loggedin"] = true;
                executePlainSQL("insert into usersSubscribeTeam values (" . $_POST['subscribeID'] . ", '" . $_POST['subscribeTN'] . "', '" . $_POST['subscribeLN'] . "')");
                OCICommit($db_conn); 
            } elseif (array_key_exists('unsubscribePLsubmit', $_POST)) {
                $result = executePlainSQL("select player_id from playerSignContract where name ='" . $_POST['subscribePL'] . "'");
                $player_id = null;
                while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                    $player_id = $row["PLAYER_ID"];
                }
                executePlainSQL("delete from usersSubscribePlayer where users_id=" . $_POST['subscribeID'] . " and player_id=" . $player_id);
                OCICommit($db_conn);
            } elseif (array_key_exists('unsubscribeTMsubmit', $_POST)) {
                executePlainSQL("delete from usersSubscribeTeam where users_id=" . $_POST['subscribeID'] . " and team_name='" . $_POST['subscribeTN'] . "' and league_name='" . $_POST['subscribeLN'] . "'");
                OCICommit($db_conn); 
            } elseif (array_key_exists('filtersubmit', $_POST)) {
                global $filter_result;
                $_SESSION["filter_received"] = true;
                $query_str = "select * from matches";
                $append_query = array();
                if ($_POST['filterSDATE'] != "") {
                    array_push($append_query, " mdate>'" . $_POST['filterSDATE'] . "'");
                }
                if ($_POST['filterEDATE'] != "") {
                    array_push($append_query, " mdate<'" . $_POST['filterEDATE'] . "'");
                }
                if ($_POST['filterAN'] != "") {
                    array_push($append_query, " arena_name='" . $_POST['filterAN'] . "'");
                }
                if ($_POST['filterLN'] != "") {
                    $_SESSION["league_exist"] = true;
                    array_push($append_query, " league_name='" . $_POST['filterLN'] . "'");
                }
                if ($_POST['filterTMA'] == "" and $_POST['filterTMB'] == "") {
                    echo "";
                } elseif ($_POST['filterTMA'] == "") {
                    array_push($append_query, " teamA_name='" . $_POST['filterTMB'] . "' or teamB_name='" . $_POST['filterTMB'] . "'");
                } elseif ($_POST['filterTMB'] == "") {
                    array_push($append_query, " teamA_name='" . $_POST['filterTMA'] . "' or teamB_name='" . $_POST['filterTMA'] . "'");
                } else {
                    array_push($append_query, " (teamA_name='" . $_POST['filterTMA'] . "' and teamB_name='" . $_POST['filterTMB'] . "') or (teamA_name='" . $_POST['filterTMB'] . "' and teamB_name='" . $_POST['filterTMA'] . "')" );
                }
                if ($_POST['filterPL'] != "") {
                    array_push($append_query, " name='" . $_POST['filterPL'] . "'");
                }
                if ($_POST['filterCO'] != "") {
                    array_push($append_query, " name='" . $_POST['filterCO'] . "'");
                }
                if (sizeof($append_query) != 0) {
                    $query_str = $query_str . " where ";
                    $array_length = count($append_query);
                    $index = 0;
                    foreach($append_query as $elem) {
                        $index++;
                        $query_str = $query_str . $elem;
                        if ($index != $array_length) {
                            $query_str = $query_str . " and ";
                        }
                    }
                }
                executePlainSQL("alter session set NLS_TIMESTAMP_FORMAT='yyyy-mm-dd hh24:mi:ss:ff'");
                $result = executePlainSQL($query_str);
                $filter_result = array();
                while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                    array_push($filter_result, $row);
                }
                // $test = executePlainSQL("select ply.name, p.score, p.assist, ply.position, c.price from playerSignContract ply, perform p, contracts c where ply.player_id = p.player_id and ply.team_name = p.teamA_name and ply.contract_no = c.contract_no and p.mdate ='2018-11-04 18:30:00.000000' and p.teamA_name ='Toronto Raptors' and p.teamB_name ='Los Angeles Lakers' order by p.score");
                // while($row = OCI_Fetch_Array($test, OCI_BOTH)) {
                //     echo "<p>" . $row["NAME"] . "</p>";
                // }
            } else
                if (array_key_exists('updatesubmit', $_POST)) {
                    // Update tuple using data from user
                    $tuple = array (
                        ":bind1" => $_POST['oldLocation'],
                        ":bind2" => $_POST['newLocation'],
                        ":bind3" => $_POST['oldCapacity'],
                        ":bind4" => $_POST['newCapacity']
                    );
                    $alltuples = array (
                        $tuple
                    );
                    executeBoundSQL("update arenas set location=:bind2, capacity=:bind4 where location=:bind1 and capacity=:bind3", $alltuples);
                    OCICommit($db_conn);
    
                } elseif (array_key_exists('deletesubmit', $_POST)) {
                    // Delete tuple using data from user 
                    // Here just suppose that there is a button with deletesubmit as its name
                    $tuple = array (
                        ":bind1" => $_POST['delAN'],
                        ":bind2" => $_POST['delLocation'],
                        ":bind3" => $_POST['delCapacity']
                    );
                    $alltuples = array (
                        $tuple
                    );
                    executeBoundSQL("delete from arenas where arena_name=:bind1 and location=:bind2 and capacity=:bind3", $alltuples);
                    OCICommit($db_conn);
                    
                } else
                    if (array_key_exists('dostuff', $_POST)) {
                        // Insert data into table...
                        $list1 = array (
                            ":bind1" => "Arena1",
                            ":bind2" => "Vancouver",
                            ":bind3" => 30
                        );
                        $list2 = array (
                            ":bind1" => "Arena2",
                            ":bind2" => "Toronto",
                            ":bind3" => 50
                        );
                        $allrows = array (
                            $list1,
                            $list2
                        );
                        executeBoundSQL("insert into arenas values (:bind1, :bind2, :bind3)", $allrows); //the function takes a list of lists
                        // Inserting data into table using bound variables
                        $list1 = array (
                            ":bind1" => "League1"
                        );
                        $list2 = array (
                            ":bind1" => "League2"
                        );
                        $allrows = array (
                            $list1,
                            $list2
                        );
                        executeBoundSQL("insert into leagues values (:bind1)", $allrows); //the function takes a list of lists
                        // Update data...
                        //executePlainSQL("update arenas set nid=10 where nid=2");
                        // Delete data...
                        OCICommit($db_conn);
                    }
    
        if ($_POST && $success) {
            //POST-REDIRECT-GET -- See http://en.wikipedia.org/wiki/Post/Redirect/Get
            // header("location: sports-app.php");
            // exit();
        } else {
            // Select data...
            $result = executePlainSQL("select * from matches");
        }
    
        //Commit to save changes...
        OCILogoff($db_conn);
    } else {
        echo "cannot connect";
        $e = OCI_Error(); // For OCILogon errors pass no handle
        echo htmlentities($e['message']);
    }

    function render_date_input() {
        echo "<p>Start Date: <input type='date' class='form-control' name='filterSDATE'></p>";
        echo "<p>End Date: <input type='date' class='form-control' name='filterEDATE'></p>";
    }
    
    function render_arena_dropdown() {
        echo "<p>Arena: ";
        $result = executePlainSQL("select * from arenas");
        echo "<select class='form-control' name='filterAN'>";
        echo "<option value=''></option>";
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<option value='" . $row["ARENA_NAME"] . "'>" . $row["ARENA_NAME"] . "</option>";
        }
        echo "</select>";
        echo "</p>";
    }
    
    function render_league_dropdown() {
        echo "<p>League: ";
        $result = executePlainSQL("select * from leagues");
        echo "<select class='form-control' name='filterLN'>";
        echo "<option value=''></option>";
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<option value='" . $row["LEAGUE_NAME"] . "'>" . $row["LEAGUE_NAME"] . "</option>";
        }
        echo "</select>";
        echo "</p>";
    }
    
    function render_team_dropdown() {
        echo "<p>Team: ";
        $result = executePlainSQL("select * from teamIn");
        echo "<select class='form-control' name='filterTMA'>";
        echo "<option value=''></option>";
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<option value='" . $row["TEAM_NAME"] . "'>" . $row["TEAM_NAME"] . "</option>";
        }
        echo "</select>";
        echo "<p> vs </p>";
        echo "<select class='form-control' name='filterTMB'>";
        echo "<option value=''></option>";
        $result = executePlainSQL("select * from teamIn");
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<option value='" . $row["TEAM_NAME"] . "'>" . $row["TEAM_NAME"] . "</option>";
        }
        echo "</select>";
        echo "</p>";
    }
    
    function render_player_dropdown() {
        echo "<p>Player: ";
        $result = executePlainSQL("select * from playerSignContract");
        echo "<select class='form-control' name='filterPL'>";
        echo "<option value=''></option>";
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<option value='" . $row["NAME"] . "'>" . $row["NAME"] . "</option>";
        }
        echo "</select>";
        echo "</p>";
    }
    
    function render_coach_dropdown() {
        echo "<p>Coach: ";
        $result = executePlainSQL("select * from coachSignContract");
        echo "<select class='form-control' name='filterCO'>";
        echo "<option value=''></option>";
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<option value='" . $row["NAME"] . "'>" . $row["NAME"] . "</option>";
        }
        echo "</select>";
        echo "</p>";
    }

    function render_insertion_form() {
        if ($_SESSION["isAdmin"] == 1) {
            // echo "<form method='POST' action='sports-app.php'>";
            // echo "<p>Arena</p>";
            echo '<div id="insertion-form">
                <h3 class="form-title">Insert New Data</h3>
                <form method="POST" action="sports-app.php">
                    <p>Arena</p>
                    <p><input class="form-control" type="text" name="insAN" size="12" placeholder="Arena Name"><input class="form-control" type="text" name="insLocation" size="12" placeholder="Location"><input class="form-control" type="number" name="insCapacity" size="6" placeholder="Capacity"><input class="btn btn-primary" type="submit" name="arenainsertsubmit" value="Submit"></p>
                </form>
                <form method="POST" action="sports-app.php">
                    <p>League</p>
                    <p><input class="form-control" type="text" name="insLN" size="12" placeholder="League Name"><input class="btn btn-primary" type="submit" name="leagueinsertsubmit" value="Submit"></p>
                </form>
                <form method="POST" action="sports-app.php">
                    <p>Match</p>
                    <p><input class="form-control" type="date" name="insMatchD" size="6" placeholder="Date"><input class="form-control" type="text" name="insMatchTAN" size="12" placeholder="TeamA Name"><input class="form-control" type="text" name="insMatchTBN" size="12" placeholder="TeamB Name"><input class="form-control" type="text" name="insMatchAN" size="12" placeholder="Arena Name"><input class="form-control" type="number" name="insMatchTP" size="6" placeholder="Ticket Price"><input class="form-control" type="text" name="insMatchLN" size="12" placeholder="League Name"><input class="btn btn-primary" type="submit" name="matchinsertsubmit" value="Submit"></p>
                </form>
                <form method="POST" action="sports-app.php">
                    <p>Player</p>
                    <p><input class="form-control" type="number" name="insPlayerID" size="6" placeholder="Player ID"><input class="form-control" type="date" name="insPlayerDoB" size="6" placeholder="Player Date of Birth"><input class="form-control" type="text" name="insPlayerP" size="12" placeholder="Player Position"><input class="form-control" type="text" name="insPlayerN" size="12" placeholder="Player Name"><input class="form-control" type="number" name="insPlayerContactNo" size="6" placeholder="Contact Number"><input class="form-control" type="text" name="insPlayerTN" size="12" placeholder="Team Name"><input class="form-control" type="text" name="insPlayerLN" size="12" placeholder="League Name"><input class="btn btn-primary" type="submit" name="playerinsertsubmit" value="Submit"></p>
                </form>
                </div>';
        }
    }
    
    function render_delete_form() {
        if ($_SESSION["isAdmin"] == 1) {
            echo "<div id='delete-form'>";
            echo "<h3 class='form-title'>Delete Data</h3>";
            echo "<form method='POST' action='sports-app.php'>";
            echo "<p>Arena</p>";
            echo '<p><input class="form-control" type="text" name="delAN" size="12" placeholder="Arena Name"></p>';
            echo "</form>";
            echo "<form method='POST' action='sports-app.php'>";
            echo "<p>League</p>";
            echo '<p><input class="form-control" type="text" name="delLN" size="12" placeholder="League Name"></p>';
            echo "</form>";
            echo "<form method='POST' action='sports-app.php'>";
            echo "<p>Match</p>";
            echo '<p><input class="form-control" type="date" name="delMatchD" size="6" placeholder="Date"><input class="form-control" type="text" name="delMatchTAN" size="12" placeholder="TeamA Name"><input class="form-control" type="text" name="delMatchTBN" size="12" placeholder="TeamB Name"></p>';
            echo "</form>";
            echo "</div>";
        }
    }

    function render_filter_form() {
        echo "<div id='filter-form'>";
        echo "<h3 class='form-title'>Filter Matches</h3>";
        echo "<form method='POST' action='sports-app.php'>";
        render_date_input();
        render_arena_dropdown();
        render_league_dropdown();
        render_team_dropdown();
        echo "<input class='btn btn-primary' type='submit' name='filtersubmit' value='Filter'>";
        echo "</form>";
        echo "</div>";
    }

    function render_default_result() {
        global $filter_result;
        $filter_result = array();
        $result = executePlainSQL("select * from default_display");
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            array_push($filter_result, $row);
        }
        render_filter_result();
        $filter_result = null;
    }
    
    function render_filter_result() {
        global $filter_result;
        global $i;
        if ($_SESSION["league_exist"] === true) {
            $top_player = executePlainSQL('select name from playerSignContract ply where ply.league_name = \'' . $filter_result[0]["LEAGUE_NAME"] . '\' and ply.name not in (select distinct r1.player_name 
            from (select ply.name player_name, sum(p.score) sumScore
            from perform p, playerSignContract ply
            where p.player_id = ply.player_id and ply.league_name = \'' . $filter_result[0]["LEAGUE_NAME"] . '\'
            group by (ply.name, ply.league_name))r1, (select ply.name player_name, sum(p.score) sumScore
            from perform p, playerSignContract ply
            where p.player_id = ply.player_id and ply.league_name = \'' . $filter_result[0]["LEAGUE_NAME"] . '\'
            group by (ply.name, ply.league_name)) r2
            where r2.sumScore > r1.sumScore)');
            while ($player = OCI_Fetch_Array($top_player, OCI_BOTH)) {
                echo "<p class='top-player'><i class='fa fa-trophy'></i>" . $player["NAME"] . "</p>";
                echo "<p class='center'>(Best Player of " . $filter_result[0]["LEAGUE_NAME"] . ")</p>";
            }
        }
        $id = $i;
        echo '<div class="accordion" id="accordionExample' . $id . '">';
        foreach ($filter_result as $row) {
            $res = executePlainSQL('select ply.name, ply.player_id from (playerSignContract) ply where ply.team_name=\'' . trim($row["TEAMA_NAME"], " ") . '\' and ply.player_id not in (select ply.player_id from playerSignContract ply, (select mdate, teamA_name, teamB_name from matches where teamA_name =\'' . trim($row["TEAMA_NAME"], " ") . '\' or teamB_name = \'' . trim($row["TEAMA_NAME"], " ") . '\') teamMatches where ply.team_name =\'' . trim($row["TEAMA_NAME"], " ") . '\' and (ply.player_id, teamMatches.mdate, teamMatches.teamA_name, teamMatches.teamB_name) not in (select player_id, mdate, teamA_name, teamB_name from perform))');
            $best_teamA_player_id = null;
            while ($player = OCI_Fetch_Array($res, OCI_BOTH)) {
                $best_teamA_player_id = $player["PLAYER_ID"];
            }
            $detail_result = executePlainSQL("select ply.player_id, ply.name, p.score, p.assist, ply.position, c.price from playerSignContract ply, perform p, contracts c where ply.player_id = p.player_id and ply.team_name = p.teamA_name and ply.contract_no = c.contract_no and p.mdate ='" . $row['MDATE'] . "' and p.teamA_name ='" . trim($row['TEAMA_NAME'], " ") . "' and p.teamB_name ='" . trim($row['TEAMB_NAME'], " ") . "' order by p.score");
            echo '<div class="card">';
            echo '<div class="card-header" id="headingOne">';
            echo '<h5 class="mb-0">';
            echo '<button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse'.$i.'" aria-expanded="true" aria-controls="collapse'.$i.'">';
            echo "<p class='match-display'><strong>" . substr($row["MDATE"], 0, 10) . " " . $row["TEAMA_NAME"] . " (" . $row["TEAMA_SCORE"] . " - " . $row["TEAMB_SCORE"] . ") " . $row["TEAMB_NAME"] . "</strong></p>";
            echo '</button>';
            echo '</h5>';
            echo '</div>';
            echo '<div id="collapse'.$i.'" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample' . $id . '">';
            echo '<div class="card-body">';
            echo "<p>The player with <i class='fa fa-star'></i> shows up on field in every match of the team</p>";
            echo '<h3>' . $row["TEAMA_NAME"] . '</h3>';
            echo '<table frame="box">';
            echo '<tr><th>Name</th><th>Score</th><th>Assist</th><th>Position</th><th>Contract Price</th></tr>';
            while ($inner_row = OCI_Fetch_Array($detail_result, OCI_BOTH)) {
                if ($best_teamA_player_id != null && $best_teamA_player_id == $inner_row["PLAYER_ID"]) {
                    echo '<tr><td>' . '<i class="fa fa-star"></i> ' . $inner_row["NAME"] . '</td><td>' . $inner_row["SCORE"] . '</td><td>' . $inner_row["ASSIST"] . '</td><td>' . $inner_row["POSITION"] . '</td><td>' . $inner_row["PRICE"] . '</td></tr>';
                } else {
                    echo '<tr><td>' . $inner_row["NAME"] . '</td><td>' . $inner_row["SCORE"] . '</td><td>' . $inner_row["ASSIST"] . '</td><td>' . $inner_row["POSITION"] . '</td><td>' . $inner_row["PRICE"] . '</td></tr>';
                }
            }            
            echo '</table>';
            $coach_result = executePlainSQL("select name from coachSignContract where team_name='" . $row["TEAMA_NAME"]. "'");
            while ($coach = OCI_Fetch_Array($coach_result, OCI_BOTH)) {
                echo "<p class='coach'>Coach: " . $coach["NAME"] . "</p>";
            }
            $res = executePlainSQL('select ply.name, ply.player_id from (playerSignContract) ply where ply.team_name=\'' . trim($row["TEAMB_NAME"], " ") . '\' and ply.player_id not in (select ply.player_id from playerSignContract ply, (select mdate, teamA_name, teamB_name from matches where teamA_name =\'' . trim($row["TEAMB_NAME"], " ") . '\' or teamB_name = \'' . trim($row["TEAMB_NAME"], " ") . '\') teamMatches where ply.team_name =\'' . trim($row["TEAMB_NAME"], " ") . '\' and (ply.player_id, teamMatches.mdate, teamMatches.teamA_name, teamMatches.teamB_name) not in (select player_id, mdate, teamA_name, teamB_name from perform))');
            $best_teamB_player_id = null;
            while ($player = OCI_Fetch_Array($res, OCI_BOTH)) {
                $best_teamB_player_id = $player["PLAYER_ID"];
            }
            $detail_result = executePlainSQL("select ply.player_id, ply.name, p.score, p.assist, ply.position, c.price from playerSignContract ply, perform p, contracts c where ply.player_id = p.player_id and ply.team_name = p.teamB_name and ply.contract_no = c.contract_no and p.mdate ='" . $row['MDATE'] . "' and p.teamA_name ='" . trim($row['TEAMA_NAME'], " ") . "' and p.teamB_name ='" . trim($row['TEAMB_NAME'], " ") . "' order by p.score");
            echo '<h3>' . $row["TEAMB_NAME"] . '</h3>';
            echo '<table frame="box">';
            echo '<tr><th>Name</th><th>Score</th><th>Assist</th><th>Position</th><th>Contract Price</th></tr>';
            while ($inner_row = OCI_Fetch_Array($detail_result, OCI_BOTH)) {
                if ($best_teamB_player_id != null && $best_teamB_player_id == $inner_row["PLAYER_ID"]) {
                    echo '<tr><td>' . '<i class="fa fa-star"></i> ' . $inner_row["NAME"] . '</td><td>' . $inner_row["SCORE"] . '</td><td>' . $inner_row["ASSIST"] . '</td><td>' . $inner_row["POSITION"] . '</td><td>' . $inner_row["PRICE"] . '</td></tr>';
                } else {
                    echo '<tr><td>' . $inner_row["NAME"] . '</td><td>' . $inner_row["SCORE"] . '</td><td>' . $inner_row["ASSIST"] . '</td><td>' . $inner_row["POSITION"] . '</td><td>' . $inner_row["PRICE"] . '</td></tr>';
                }            }            
            echo '</table>';
            $coach_result = executePlainSQL("select name from coachSignContract where team_name='" . $row["TEAMB_NAME"]. "'");
            while ($coach = OCI_Fetch_Array($coach_result, OCI_BOTH)) {
                echo "<p class='coach'>Coach: " . $coach["NAME"] . "</p>";
            }
            echo '</div>';
            echo '</div>';
            echo '</div>';
            $i += 1;
        }
        echo '</div>';
    }

    function render_subscribe_result() {
        if (isset($_SESSION["id"])) {
            global $filter_result;
            executePlainSQL("alter session set NLS_TIMESTAMP_FORMAT='yyyy-mm-dd hh24:mi:ss:ff'");
            $result = executePlainSQL("select Q.mdate mdate, Q.teamA_name teamA_name, Q.teamA_score teamA_score, Q.teamB_name teamB_name, Q.teamB_score teamB_score
            from matches Q, usersSubscribeTeam U
            where U.users_id = " . $_SESSION["id"] . " and U.team_name = Q.teamA_name or U.team_name = Q.teamB_name
            order by mdate DESC");
            $filter_result = array();
            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                array_push($filter_result, $row);
            }
            render_filter_result();
            $filter_result = null;
        }
    }

    function show_perform() {
        // executePlainSQL("insert into perform values('2018-11-02 19:00:00', 'San Antonio Spurs', 'Sacramento Kings', 1003, 23, 8)");
        // OCICommit($db_conn);
        $result = executePlainSQL("select * from perform");
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<p>Date: " . $row["MDATE"] . " TeamA: " . $row["TEAMA_NAME"] . " TeamB: " . $row["TEAMB_NAME"] . "</p>";
        }
    }

    function render_subscribe_player_form() {
        echo "<div id='subscribe-player'>";
        echo "<h3 class='form-title'>Subscribe Player</h3>";
        echo "<form method='POST' action='sports-app.php'>";
        echo "<input hidden type='text' name='subscribeID' value='" . $_SESSION["id"] . "'>";
        echo "<select class='form-control' name='subscribePL'>";
        $result = executePlainSQL("select name from playerSignContract");
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<option value='" . $row["NAME"] . "'>" . $row["NAME"] . "</option>";
        }
        echo "</select>";
        echo "<br>";
        echo "<input class='btn btn-primary' type='submit' name='subscribePLsubmit' value='Subscribe'>";
        echo "<input class='btn btn-danger' type='submit' name='unsubscribePLsubmit' value='Unsubscribe'>";
        echo "</form>";
        echo "<a href='#' id='subscribe-player-button'>Click here to subscribe by team name</a>";
        echo "</div>";
    }

    function render_subscribe_team_form() {
        echo "<div id='subscribe-team'>";
        echo "<h3 class='form-title'>Subscribe Team</h3>";
        echo "<form method='POST' action='sports-app.php'>";
        echo "<input hidden type='text' name='subscribeID' value='" . $_SESSION["id"] . "'>";
        echo "<select class='form-control' name='subscribeTN'>";
        $result = executePlainSQL("select team_name from teamIn");
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<option value='" . $row["TEAM_NAME"] . "'>" . $row["TEAM_NAME"] . "</option>";
        }
        echo "</select>";
        echo "<br>";
        echo "<select class='form-control' name='subscribeLN'>";
        $result = executePlainSQL("select distinct league_name from teamIn");
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<option value='" . $row["LEAGUE_NAME"] . "'>" . $row["LEAGUE_NAME"] . "</option>";
        }
        echo "</select>";
        echo "<br>";
        echo "<input class='btn btn-primary' type='submit' name='subscribeTMsubmit' value='Subscribe'>";
        echo "<input class='btn btn-danger' type='submit' name='unsubscribeTMsubmit' value='Unsubscribe'>";
        echo "</form>";
        echo "<a href='#' id='subscribe-team-button'>Click here to subscribe by player name</a>";
        echo "</div>";
    }

    function render_header() {
        if ($_SESSION["id_does_not_exist"] === true) {
            echo "<span class='error'>The ID does not exist</span>";
        } else if ($_SESSION["id_is_empty"] === true) {
            echo "<span class='error'>The ID cannot be empty</span>";
        } else if ($_SESSION["id_already_taken"] === true) {
            echo "<span class='error'>The ID has already been taken</span>";
        }
        if ($_SESSION["signup_success"] === true) {
            echo "<p class='success'>You successfully signed up</p>";
        }
        if ($_SESSION["loggedin"] === true) {
            echo "<p>You are logged in with id: " . $_SESSION["id"] . "</p>";
        }
        $_SESSION["id_does_not_exist"] = false;
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel='stylesheet' type='text/css' href='style.css' />
    <script src="https://code.jquery.com/jquery-3.3.1.js" integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
    <script src="script.js"></script>
    <?php
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            echo '<script src="animation.js"></script>';
        }
    ?>
    <title>Sports App</title>
</head>

<body>
    <div id="header"><?php render_header(); ?></div>
    <div id="container">
        <div id="left-container">
            <?php
                if ($_SESSION["filter_received"] === true) {
                    echo "<h1>Filter</h1>";
                    render_filter_result();
                } else {
                    echo "<h1>PIN</h1>";
                    render_subscribe_result();  
                    echo "<h1>Default</h1>";
                    render_default_result();
                }
            ?>
        </div>
        <div id="right-container">
            <div id="login-container">
                <div id="login">
                    <form method="POST" action="sports-app.php">
                        <h3 class="form-title">Login</h3>
                        <p><input type="number" class="form-control" name="loginID" placeholder="Your ID" size="12"></p>
                        <p><input type="submit" class="btn btn-primary" value="Login" name="login"></p>
                    </form>
                    <a href="#" id="login-change-button">Don't have a account? Sign up here</a>
                </div>
                <div id="signup">
                    <form method="POST" action="sports-app.php">
                        <h3 class="form-title">Sign Up</h3>
                        <p><input type="number" class="form-control" name="signupID" placeholder="Your ID" size="12"></p>
                        <p><input type="submit" class="btn btn-primary" value="Sign Up" name="signup"></p>
                    </form>
                    <a href="#" id="signup-change-button">Already have an account? Login here</a>
                </div>
            </div>
            <div>
                <?php
                    if ($_SESSION["loggedin"]) {
                        render_subscribe_team_form();
                        render_subscribe_player_form();
                    }
                ?>
            </div>
            <div>
                <?php
                    render_insertion_form();
                    render_delete_form();
                    render_filter_form();
                ?>
            </div>
        </div>
    </div>
    <div id="introduction">
        <p>Welcome to our Sports App</p>
    </diV>
</body>

</html>


