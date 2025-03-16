<?php
include "conexao.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["adicionar"])) {
    $materia = $_POST["materia"];
    $atividade = $_POST["atividade"];
    $data = $_POST["data"];

    $sql = "INSERT INTO atividades (materia, atividade, data_entrega) VALUES ('$materia', '$atividade', '$data')";
    $conn->query($sql);
}

if (isset($_GET["marcar"])) {
    $id = intval($_GET["marcar"]);
    $conn->query("UPDATE atividades SET entregue = 1 WHERE id = $id");
}

if (isset($_GET["excluir"])) {
    $id = intval($_GET["excluir"]);
    $conn->query("DELETE FROM atividades WHERE id = $id");
}

if (isset($_GET["editar"])) {
    $id = intval($_GET["editar"]);
    $materia = $conn->real_escape_string($_GET["materia"]);
    $atividade = $conn->real_escape_string($_GET["atividade"]);
    $data = $_GET["data"];

    $conn->query("UPDATE atividades SET materia='$materia', atividade='$atividade', data_entrega='$data' WHERE id=$id");
}

$filtro = isset($_GET["filtro"]) ? $_GET["filtro"] : "";
$sql = "SELECT * FROM atividades";
if ($filtro == "pendente") {
    $sql .= " WHERE entregue = 0";
} elseif ($filtro == "concluido") {
    $sql .= " WHERE entregue = 1";
}
$sql .= " ORDER BY data_entrega ASC";
$atividades = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador de AVAs</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>📚 Gerenciador de AVAs</h1>

        <form method="POST">
            <input type="text" name="materia" placeholder="Matéria" required>
            <input type="text" name="atividade" placeholder="Atividade" required>
            <input type="date" name="data" required>
            <button type="submit" name="adicionar">➕ Adicionar</button>
        </form>

        <form method="GET">
            <label>Filtrar por:</label>
            <select name="filtro" onchange="this.form.submit()">
                <option value="">Todas</option>
                <option value="pendente" <?= ($filtro == 'pendente') ? 'selected' : '' ?>>Pendentes</option>
                <option value="concluido" <?= ($filtro == 'concluido') ? 'selected' : '' ?>>Concluídas</option>
            </select>
        </form>

        <div class="lista">
            <?php while ($row = $atividades->fetch_assoc()): ?>
                <?php
                $hoje = date('Y-m-d');
                $dias_restantes = (strtotime($row["data_entrega"]) - strtotime($hoje)) / 86400;
                $classe_prazo = ($dias_restantes <= 3 && !$row['entregue']) ? 'proximo-prazo' : '';
                ?>
                <div class="atividade <?= $row['entregue'] ? 'entregue' : '' ?> <?= $classe_prazo ?>">
                    <h2><?= htmlspecialchars($row["materia"]) ?></h2>
                    <p><strong>Atividade:</strong> <?= htmlspecialchars($row["atividade"]) ?></p>
                    <p><strong>Data de entrega:</strong> <?= $row["data_entrega"] ?></p>
                    <div class="acoes">
                        <?php if (!$row['entregue']): ?>
                            <a href="?marcar=<?= $row['id'] ?>" class="marcar">✔ Marcar como entregue</a>
                        <?php endif; ?>
                        <a href="#" onclick="editarAtividade(<?= $row['id'] ?>, '<?= htmlspecialchars($row['materia']) ?>', '<?= htmlspecialchars($row['atividade']) ?>', '<?= $row['data_entrega'] ?>')" class="editar">✏️ Editar</a>
                        <a href="#" onclick="confirmarExclusao(<?= $row['id'] ?>)" class="excluir">🗑 Excluir</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script>
        function confirmarExclusao(id) {
            if (confirm("Tem certeza que deseja excluir esta atividade?")) {
                window.location.href = "?excluir=" + id;
            }
        }

        function editarAtividade(id, materia, atividade, data) {
            let novaMateria = prompt("Nova Matéria:", materia);
            let novaAtividade = prompt("Nova Atividade:", atividade);
            let novaData = prompt("Nova Data (YYYY-MM-DD):", data);

            if (novaMateria && novaAtividade && novaData) {
                window.location.href = "?editar=" + id + "&materia=" + encodeURIComponent(novaMateria) + "&atividade=" + encodeURIComponent(novaAtividade) + "&data=" + novaData;
            }
        }
    </script>
</body>
</html>