<?php


namespace App\Model;


class ProjectManager extends AbstractManager
{
    const TABLE = 'project';

    /**
     *  Initializes this class.
     */
    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    public function selectMainPictureProjectByType(string $projectType)
    {
        $statement = $this->pdo->query("SELECT * FROM $this->table JOIN " . PictureManager::TABLE .
            " ON project.id=picture.project_id WHERE is_main=1 AND type_of_project=$projectType");
        return $statement->fetchAll();
    }

    public function selectMainPictureProjectFavorite()
    {
        $statement = $this->pdo->query("SELECT * FROM $this->table JOIN " . PictureManager::TABLE .
            " ON project.id=picture.project_id WHERE is_main=1 AND is_favorite");
        return $statement->fetchAll();
    }

    public function selectInfoProjectByIdProject(int $id)
    {
        $statement = $this->pdo->prepare("SELECT project.id, project.title, project.description, project.promo,
        project.type_of_project, project.is_favorite, language.name
        FROM $this->table
        JOIN language ON language.id = project.language_id
        WHERE project.id=:id");
        $statement->bindValue('id', $id, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch();
    }

    public function insert(array $project): int
    {
        // prepared request
        $statement = $this->pdo->prepare("INSERT INTO " . self::TABLE .
            " (`title`, `description`, `promo`, `type_of_project`, `language_id`, `is_favorite` ) 
            VALUES (:title, :description, :promo, :type_of_project, :language_id, :is_favorite)");
        $statement->bindValue('title', $project['title'], \PDO::PARAM_STR);
        $statement->bindValue('description', $project['description'], \PDO::PARAM_STR);
        $statement->bindValue('promo', $project['promo'], \PDO::PARAM_STR);
        $statement->bindValue('type_of_project', $project['type_of_project'], \PDO::PARAM_INT);
        $statement->bindValue('language_id', $project['language_id'], \PDO::PARAM_INT);
        $statement->bindValue('is_favorite', $project['is_favorite'], \PDO::PARAM_BOOL);

        if ($statement->execute()) {
            return (int)$this->pdo->lastInsertId();
        }
    }

    public function update(array $project): bool
    {
        // prepared request
        $statement = $this->pdo->prepare("UPDATE " . self::TABLE .
            " SET `title` = :title, `description` = :description, `promo` = :promo,
            `type_of_project` =  :type_of_project, `language_id` = :language_id, `is_favorite` = :is_favorite 
            WHERE `id` = :id");
        $statement->bindValue('id', $project['id'], \PDO::PARAM_INT);
        $statement->bindValue('title', $project['title'], \PDO::PARAM_STR);
        $statement->bindValue('description', $project['description'], \PDO::PARAM_STR);
        $statement->bindValue('promo', $project['promo'], \PDO::PARAM_STR);
        $statement->bindValue('type_of_project', $project['type_of_project'], \PDO::PARAM_INT);
        $statement->bindValue('language_id', $project['language_id'], \PDO::PARAM_INT);
        $statement->bindValue('is_favorite', $project['is_favorite'], \PDO::PARAM_BOOL);

        return $statement->execute();
    }

    public function selectByWordKey($word): array
    {
        $statement = $this->pdo->prepare("SELECT project.id, project.title, project.description,
        picture.name, picture.is_main
        FROM " . self::TABLE . "
        JOIN picture
        ON picture.project_id = project.id
        WHERE picture.is_main = True
        AND (project.title LIKE :word
        OR project.description LIKE :word)");
        $statement->bindValue(':word', '%' . $word . '%', \PDO::PARAM_STR);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function updateFavoriteByProjectId($jsonData)
    {
        $query = "UPDATE " . self::TABLE .
            " SET `is_favorite` = :is_favorite
            WHERE `id` = :id";
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':is_favorite', $jsonData['favorite'], \PDO::PARAM_BOOL);
        $statement->bindValue(':id', $jsonData['project'], \PDO::PARAM_INT);

        return $statement->execute();
    }
}
