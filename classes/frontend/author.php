<?php

namespace PUBS {

    use MeekroDB;
    use PUBS\Utils as PUBSUTILS;
    use WhereClause;

    class Author extends Pubs_Base implements \JsonSerializable
    {
        private $_id;
        private $_peopleId;
        private $_authornumber;
        private $_libraryId;
        private $_student;

        // The following come from the People Table
        private $_FirstName;
        private $_LastName;
        private $_SuffixName;
        // private $_DateCreated;
        // private $_DateModified;

        protected function id($value = null)
        {
            // If value was provided, set the value
            if ($value) {
                $this->_id = $value;
            }
            // If no value was provided return the existing value
            else {
                return $this->_id;
            }
        }
        protected function peopleId($value = null)
        {
            // If value was provided, set the value
            if ($value) {
                $this->_peopleId = $value;
            }
            // If no value was provided return the existing value
            else {
                return $this->_peopleId;
            }
        }
        protected function authornumber($value = null)
        {
            // If value was provided, set the value
            if ($value) {
                $this->_authornumber = $value;
            }
            // If no value was provided return the existing value
            else {
                return $this->_authornumber;
            }
        }
        protected function libraryId($value = null)
        {
            // If value was provided, set the value
            if ($value) {
                $this->_libraryId = $value;
            }
            // If no value was provided return the existing value
            else {
                return $this->_libraryId;
            }
        }

        protected function student($value = null)
        {
            // If value was provided, set the value
            if ($value) {
                $this->_student = $value;
            }
            // If no value was provided return the existing value
            else {
                return $this->_student;
            }
        }

        protected function FirstName($value = null)
        {
            // If value was provided, set the value
            if ($value) {
                $this->_FirstName = $value;
            }
            // If no value was provided return the existing value
            else {
                return $this->_FirstName;
            }
        }
        protected function LastName($value = null)
        {
            // If value was provided, set the value
            if ($value) {
                $this->_LastName = $value;
            }
            // If no value was provided return the existing value
            else {
                return $this->_LastName;
            }
        }
        protected function SuffixName($value = null)
        {
            // If value was provided, set the value
            if ($value) {
                $this->_SuffixName = $value;
            }
            // If no value was provided return the existing value
            else {
                return $this->_SuffixName;
            }
        }

        /*
        protected function DateCreated($value = null)
        {
            // If value was provided, set the value
            if ($value) {
                $this->_DateCreated = $value;
            }
            // If no value was provided return the existing value
            else {
                return $this->_DateCreated;
            }
        }
        protected function DateModified($value = null)
        {
            // If value was provided, set the value
            if ($value) {
                $this->_DateModified = $value;
            }
            // If no value was provided return the existing value
            else {
                return $this->_DateModified;
            }
        }
        */
        public function jsonSerialize()
        {
            return [
                'id' => $this->id,
                'peopleId' => $this->peopleId,
                'authornumber' => $this->authornumber,
                'libraryId' => $this->libraryId,
                'student' => $this->student,
                'FirstName' => $this->FirstName,
                'LastName' => $this->LastName,
                'SuffixName' => $this->SuffixName
                //  'DateCreated' => $this->DateCreated,
                //  'DateModified' => $this->DateModified
            ];
        }

        public static function Get($id)
        {
            PUBSUTILS::$db->error_handler = false; // since we're catching errors, don't need error handler
            PUBSUTILS::$db->throw_exception_on_error = true;

            try {

                $row = PUBSUTILS::$db->queryFirstRow(
                    "SELECT DISTINCT
                        a.*,
                        p.id as peopleRecordID,
                        p.FirstName,
                        p.LastName,
                        p.SuffixName
                    From 
                        author a
                    INNER JOIN
	                    people p on a.peopleId = p.id
                    Where 
                       a.id = %i",
                    $id
                );
                $author = Author::populatefromRow($row);
            } catch (\MeekroDBException $e) {
                return new \WP_Error('Author_Get_Error', $e->getMessage());
            }
            return $author;
        }

        public static function GetAll($request)
        {
            PUBSUTILS::$db->error_handler = false; // since we're catching errors, don't need error handler
            PUBSUTILS::$db->throw_exception_on_error = true;

            // Create the filter:
            $filtersLogic = 'AND';
            $filterWhereStatement = new WhereClause($filtersLogic);
            if ($request['filter']) {
                $filters = $request['filter']['filters'];
                $filtersLogic = $request['filter']['logic'];
                // Build the sql statemont for the where clause.

                foreach ($filters as $filter) {
                    $field = $filter['field'];
                    $value = $filter['value'];
                    $operator = $filter['operator'];
                    $searchType = '%ss'; // Search String set as default
                    // Only continue if the filter has any value, filters can exist with no value.
                    if ($value) {
                        // Convert operators to SQL
                        if ($operator == 'contains' || $operator == 'LIKE') {
                            $operator = 'LIKE';
                            $searchType = '%ss';
                        } else if ($operator == 'eq') {
                            $operator = '=';
                            $searchType = '%s';
                        } else if ($operator == 'gte') {
                            $operator = '>=';
                            $searchType = '%s';
                        } else if ($operator == 'lte') {
                            $operator = '<=';
                            $searchType = '%s';
                        } else {
                            $operator = $operator;
                            $searchType = '%s';
                        }

                        $filterWhereStatement->add($field .  " " . $operator . " " . $searchType, $value);
                    }
                }
            }

            $authors = new NestedSerializable();

            try {
                $results = PUBSUTILS::$db->query(
                    "SELECT DISTINCT
                        a.*,
                        p.id as peopleRecordID,
                        p.FirstName,
                        p.LastName,
                        p.SuffixName
                    FROM 
                        author a
                    INNER JOIN
	                    people p on a.peopleId = p.id
                    WHERE %l
                    ORDER BY a.authornumber asc",
                    $filterWhereStatement
                );
                foreach ($results as $row) {
                    $author = Author::populatefromRow($row);
                    $authors->add_item($author);  // Add the author to the collection
                }
            } catch (\MeekroDBException $e) {
                $query = $e->getQuery();
                return new \WP_Error('Author_GetAll_Error', $e->getMessage());
            }

            return [
                'data' => $authors
            ];
        }

        public static function GetAllByLibraryId($id)
        {
            PUBSUTILS::$db->error_handler = false; // since we're catching errors, don't need error handler
            PUBSUTILS::$db->throw_exception_on_error = true;

            $authors = new NestedSerializable();

            try {
                $results = PUBSUTILS::$db->query(
                    "SELECT DISTINCT
                        a.*,
                        p.id as peopleRecordID,
                        p.FirstName,
                        p.LastName,
                        p.SuffixName
                    FROM 
                        author a
                    INNER JOIN
	                    people p on a.peopleId = p.id
                    WHERE
                        a.libraryId = %i
                    ORDER BY a.authornumber asc",
                    $id
                );
                foreach ($results as $row) {
                    $author = Author::populatefromRow($row);
                    $authors->add_item($author);  // Add the author to the collection
                }
            } catch (\MeekroDBException $e) {
                $query = $e->getQuery();
                return new \WP_Error('Author_GetAll_Error', $e->getMessage());
            }

            return $authors;
        }

        public static function populatefromrow($row)
        {
            if ($row == null)
                return null;

            $author = new Author();

            $author->id = $row['id'];
            $author->peopleId = $row['peopleId'];
            $author->authornumber = $row['authornumber'];
            $author->libraryId = $row['libraryId'];
            // $author->student = $row['student'];
            if ($row['student'] === 'true' || $row['student'] === 'on') {
                $author->student = 1;
            } else if ($row['student'] === 'false' || $row['student'] === '') {
                $author->student = 0;
            } else {
                $author->student = $row['student'];
            }
            $author->FirstName = $row['FirstName'];
            $author->LastName = $row['LastName'];
            $author->SuffixName = $row['SuffixName'];

            //   $libraryitem->DateCreated = $row['DateCreated'];
            //   $libraryitem->DateModified = $row['DateModified'];

            return $author;
        }
    }
}
