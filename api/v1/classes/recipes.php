<?php
include_once '../classes/db.php';

class Recipes
{

    function __construct()
    {
        $this->db = DB::get();
    }

    function create($data)
    {
        $query = "INSERT INTO api_recipe
                        SET
                        recipe_name=:recipe_name, prep_time=:prep_time, difficulty=:difficulty, vegetarian=:vegetarian";

        // prepare
        $stmt = $this->db->prepare($query);

        // sanitize
        if($data['recipeName']){
            $recipeName = htmlspecialchars(strip_tags($data['recipeName']));
        }
        if($data['prepTime']){
            $prepTime = htmlspecialchars(strip_tags($data['prepTime']));
        }
        if($data['difficulty']){
            $difficulty = htmlspecialchars(strip_tags($data['difficulty']));
        }
        if($data['vegetarian']){
            $vegetarian = htmlspecialchars(strip_tags($data['vegetarian']));
        }
        $vegetarian = $vegetarian ? $vegetarian : 0;

        // bind values
        $stmt->bindParam(":recipe_name", $recipeName);
        $stmt->bindParam(":prep_time", $prepTime);
        $stmt->bindParam(":difficulty", $difficulty);
        $stmt->bindParam(":vegetarian", $vegetarian);

        // execute query
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
            ;
        }

        return false;
    }

    function read($id = '')
    {
        $stmt = null;
        $result = [];
        if (! empty($id)) {
            $query = "SELECT id, recipe_name, prep_time, difficulty, vegetarian FROM api_recipe WHERE id=:id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":id", $id);
        } else {
            $query = "SELECT id, recipe_name, prep_time, difficulty, vegetarian FROM api_recipe";
            $stmt = $this->db->prepare($query);
        }
        $stmt->execute();
        if ($stmt->rowCount()) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $rowArr = array(
                    "id" => $id,
                    "recipeName" => $recipe_name,
                    "prepTime" => $prep_time,
                    "difficulty" => $difficulty,
                    "vegetarian" => $vegetarian
                );
                $result[] = $rowArr;
            }
        }
        return [
            $result,
            $stmt->rowCount()
        ];
    }

    function update($data)
    {
        $updateSql = '';
        if (! empty($data['recipeName'])) {
            $recipeName = htmlspecialchars(strip_tags($data['recipeName']));
            $updateSql .= 'recipe_name=:recipe_name,';
        }
        if (! empty($data['prepTime'])) {
            $prepTime = htmlspecialchars(strip_tags($data['prepTime']));
            $updateSql .= 'prep_time=:prep_time,';
        }
        if (! empty($data['difficulty'])) {
            $difficulty = htmlspecialchars(strip_tags($data['difficulty']));
            $updateSql .= 'difficulty=:difficulty,';
        }
        if ($data['vegetarian'] !== false) {
            $vegetarian = htmlspecialchars(strip_tags($data['vegetarian']));
            $vegetarian = $vegetarian ? $vegetarian : 0;
            $updateSql .= 'vegetarian=:vegetarian,';
        }
        $id = htmlspecialchars(strip_tags($data['id']));
        $updateSql = trim($updateSql, ',');

        $query = "UPDATE api_recipe SET $updateSql WHERE id=:id";

        // prepare
        $stmt = $this->db->prepare($query);

        // bind values
        if (! empty($recipeName)) {
            $stmt->bindParam(":recipe_name", $recipeName);
        }
        if (! empty($prepTime)) {
            $stmt->bindParam(":prep_time", $prepTime);
        }
        if (! empty($data['difficulty'])) {
            $stmt->bindParam(":difficulty", $difficulty);
        }
        if ($data['vegetarian'] !== false) {
            $stmt->bindParam(":vegetarian", $vegetarian);
        }
        $stmt->bindParam(":id", $id);

        // execute query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    function addRating($data)
    {
        $query = "INSERT INTO api_recipe_rating
                        SET
                        recipe_id=:recipe_id, rating=:rating";

        // prepare
        $stmt = $this->db->prepare($query);

        // sanitize
        $recipeId = htmlspecialchars(strip_tags($data['recipeId']));
        $rating = htmlspecialchars(strip_tags($data['rating']));

        // bind values
        $stmt->bindParam(":recipe_id", $recipeId);
        $stmt->bindParam(":rating", $rating);

        // execute query
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    function delete($data)
    {
        $id = htmlspecialchars(strip_tags($data['id']));
        $query = "delete FROM api_recipe WHERE id=:id";
        // prepare
        $stmt = $this->db->prepare($query);
        // bind values
        $stmt->bindParam(":id", $id);
        // execute query
        $result = $stmt->execute();
        if ($result) {
            return $stmt->rowCount();
        }
        return false;
    }

    function search($data)
    {
        $searchStr = trim($data['searchStr'], '{}');
        // echo $searchStr;
        $patternList = [
            '(AND|OR)?\s*(recipeName\s*?\=\s*[\'\"][^\'\"]+?[\'\"])(?:AND|OR)?',
            '(AND|OR)?\s*(prepTime[>=]{1,2}\d+)(?:AND|OR)?',
            '(AND|OR)?\s*(prepTime[<=]{1,2}\d+)(?:AND|OR)?',
            '(AND|OR)?\s*(difficulty=[\d,]+)(?:AND|OR)?',
            '(AND|OR)?\s*(vegetarian=[\d,]{1,3})(?:AND|OR)?'
        ];
        $searchSql = 'SELECT id, recipe_name, prep_time, difficulty, vegetarian FROM api_recipe WHERE 1=1 ';
        $bindParams = [];
        foreach ($patternList as $pattern) {
            $matches = [];
            if (preg_match("/$pattern/i", $searchStr, $matches)) {
                if (! empty($matches[0])) {
                    if (! empty($matches[1]) && ($matches[1] == 'AND' || $matches[1] == 'OR')) {
                        $searchSql .= " $matches[1] ";
                    } else {
                        $searchSql .= ' AND ';
                    }
                    if (! empty($matches[2])) {
                        $itemMatches = [];
                        $diffStr = '';
                        if (preg_match('/recipeName=[\'\"]([^\'\"]+?)[\'\"]/', $matches[2], $itemMatches)) {
                            $searchSql .= " recipe_name LIKE :recipe_name ";
                            $bindParams[':recipe_name'] = "%$itemMatches[1]%";
                        } else if (preg_match('/prepTime([>=]{1,2})(\d+)/', $matches[2], $itemMatches)) {
                            $searchSql .= " prep_time $itemMatches[1] :prep_time1 ";
                            $bindParams[':prep_time1'] = $itemMatches[2];
                        } else if (preg_match('/prepTime([<=]{1,2})(\d+)/', $matches[2], $itemMatches)) {
                            $searchSql .= " prep_time $itemMatches[1] :prep_time2 ";
                            $bindParams[':prep_time2'] = $itemMatches[2];
                        } else if (preg_match('/difficulty=([\d,]+)/', $matches[2], $itemMatches)) {
                            $inVals = "";
                            foreach (explode(',', $itemMatches[1]) as $idx => $dval) {
                                $key = ":did" . $idx;
                                $inVals .= "$key,";
                                $bindParams[$key] = $dval;
                            }
                            $inVals = rtrim($inVals, ",");
                            $searchSql .= " difficulty IN ( $inVals) ";
                        } else if (preg_match('/vegetarian=([\d,]+)/', $matches[2], $itemMatches)) {
                            $inVals = "";
                            foreach (explode(',', $itemMatches[1]) as $idx => $vval) {
                                $key = ":vid" . $idx;
                                $inVals .= "$key,";
                                $bindParams[$key] = $vval;
                            }
                            $inVals = rtrim($inVals, ",");
                            $searchSql .= " vegetarian IN ($inVals) ";
                        }
                    }
                }
            }
        }

        // prepare
        $stmt = $this->db->prepare($searchSql);

        // execute query
        $result = [];
        $stmt->execute($bindParams);
        if ($stmt->rowCount()) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $rowArr = array(
                    "id" => $id,
                    "recipeName" => $recipe_name,
                    "prepTime" => $prep_time,
                    "difficulty" => $difficulty,
                    "vegetarian" => $vegetarian
                );
                $result[] = $rowArr;
            }
        }
        return [
            $result,
            $stmt->rowCount()
        ];
    }
}