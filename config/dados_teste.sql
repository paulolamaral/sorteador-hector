-- Dados de teste para o sistema de sorteios (MySQL)
-- Execute este arquivo APENAS em ambiente de desenvolvimento/teste

-- Configurações para MySQL
SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';

-- Inserir participantes de exemplo
INSERT INTO participantes (
    nome, email, telefone, instagram, genero, idade, estado, cidade, 
    filhos, restaurante, tempo_hector, motivo, comprometimento, comentario
) VALUES 
('João Silva', 'joao.silva@email.com', '(11) 99999-1234', 'joaosilva', 'Masculino', '28', 'SP', 'São Paulo', 'Não', 'Restaurante Central', '2 anos', 'Crescimento profissional', 4, 'Muito motivado para aprender'),
('Maria Santos', 'maria.santos@email.com', '(21) 98888-5678', 'mariasantos', 'Feminino', '32', 'RJ', 'Rio de Janeiro', 'Sim', 'Bistro da Maria', '1 ano', 'Mudança de carreira', 5, 'Pronta para novos desafios'),
('Pedro Oliveira', 'pedro.oliveira@email.com', '(31) 97777-9012', 'pedrooliveira', 'Masculino', '25', 'MG', 'Belo Horizonte', 'Não', 'Cantina do Pedro', '6 meses', 'Aperfeiçoamento', 3, 'Buscando melhorar técnicas'),
('Ana Costa', 'ana.costa@email.com', '(47) 96666-3456', 'anacosta', 'Feminino', '30', 'SC', 'Florianópolis', 'Sim', 'Restaurante da Praia', '3 anos', 'Especialização', 5, 'Experiência sólida, quer se especializar'),
('Carlos Ferreira', 'carlos.ferreira@email.com', '(85) 95555-7890', 'carlosferreira', 'Masculino', '35', 'CE', 'Fortaleza', 'Sim', 'Churrascaria do Carlos', '4 anos', 'Liderança', 4, 'Interessado em gestão de equipes'),
('Juliana Lima', 'juliana.lima@email.com', '(11) 94444-2345', 'julianalima', 'Feminino', '27', 'SP', 'Campinas', 'Não', 'Café da Juliana', '1 ano', 'Inovação', 4, 'Quer aprender sobre novas tendências'),
('Roberto Souza', 'roberto.souza@email.com', '(51) 93333-6789', 'robertosouza', 'Masculino', '29', 'RS', 'Porto Alegre', 'Não', 'Pizzaria do Roberto', '2 anos', 'Técnicas avançadas', 3, 'Focado em melhorar habilidades culinárias'),
('Fernanda Alves', 'fernanda.alves@email.com', '(62) 92222-0123', 'fernandaalves', 'Feminino', '26', 'GO', 'Goiânia', 'Não', 'Restaurante Vegetariano', '8 meses', 'Culinária saudável', 5, 'Apaixonada por alimentação saudável'),
('Marcos Pereira', 'marcos.pereira@email.com', '(81) 91111-4567', 'marcospereira', 'Masculino', '33', 'PE', 'Recife', 'Sim', 'Frutos do Mar Marcos', '5 anos', 'Gestão', 4, 'Quer abrir próprio negócio'),
('Lucia Rodrigues', 'lucia.rodrigues@email.com', '(71) 90000-8901', 'luciarodrigues', 'Feminino', '31', 'BA', 'Salvador', 'Sim', 'Restaurante Baiano', '3 anos', 'Tradição culinária', 5, 'Especialista em culinária regional');

-- Inserir mais participantes para ter uma base maior
INSERT INTO participantes (
    nome, email, telefone, genero, idade, estado, cidade, comprometimento
) VALUES 
('Alexandre Santos', 'alexandre.santos@email.com', '(11) 99888-1111', 'Masculino', '24', 'SP', 'Santos', 3),
('Beatriz Lima', 'beatriz.lima@email.com', '(21) 98777-2222', 'Feminino', '29', 'RJ', 'Niterói', 4),
('Daniel Costa', 'daniel.costa@email.com', '(31) 97666-3333', 'Masculino', '26', 'MG', 'Uberlândia', 5),
('Elena Ferreira', 'elena.ferreira@email.com', '(47) 96555-4444', 'Feminino', '28', 'SC', 'Joinville', 4),
('Felipe Oliveira', 'felipe.oliveira@email.com', '(85) 95444-5555', 'Masculino', '30', 'CE', 'Sobral', 3),
('Gabriela Silva', 'gabriela.silva@email.com', '(11) 94333-6666', 'Feminino', '25', 'SP', 'Ribeirão Preto', 5),
('Henrique Alves', 'henrique.alves@email.com', '(51) 93222-7777', 'Masculino', '27', 'RS', 'Caxias do Sul', 4),
('Isabela Santos', 'isabela.santos@email.com', '(62) 92111-8888', 'Feminino', '32', 'GO', 'Anápolis', 3),
('Júlio Pereira', 'julio.pereira@email.com', '(81) 91000-9999', 'Masculino', '34', 'PE', 'Olinda', 4),
('Kamila Rodrigues', 'kamila.rodrigues@email.com', '(71) 90999-0000', 'Feminino', '23', 'BA', 'Feira de Santana', 5);

-- Atualizar alguns participantes com números da sorte (simulando geração prévia)
UPDATE participantes 
SET numero_da_sorte = id 
WHERE id <= 15;

-- Inserir alguns sorteios de exemplo
INSERT INTO sorteios (titulo, descricao, data_sorteio, premio, total_participantes, status) VALUES 
('Sorteio de Natal 2024', 'Sorteio especial de final de ano com prêmios incríveis', '2024-12-25', 'Vale-presente R$ 1.000,00 + Kit Chef Profissional', 15, 'agendado'),
('Workshop Exclusivo Janeiro', 'Sorteio para participar do workshop exclusivo com chef renomado', '2025-01-15', 'Workshop de 3 dias + Certificado', 15, 'agendado'),
('Equipamentos Premium', 'Sorteio de equipamentos profissionais para cozinha', '2025-02-14', 'Kit completo de facas profissionais', 15, 'agendado');

-- Simular um sorteio já realizado
INSERT INTO sorteios (titulo, descricao, data_sorteio, premio, total_participantes, status, numero_sorteado, vencedor_id) VALUES 
('Sorteio de Inauguração', 'Primeiro sorteio do sistema para celebrar o lançamento', '2024-11-01', 'Curso online + Vale-presente R$ 500,00', 10, 'realizado', 3, 3);

-- Inserir alguns logs de atividade
INSERT INTO admin_logs (acao, detalhes, ip_address) VALUES 
('Sistema Inicializado', 'Dados de teste inseridos no MySQL', '127.0.0.1'),
('Números Gerados', 'Gerados 15 números da sorte para participantes', '127.0.0.1'),
('Sorteio Criado', 'Sorteio de Natal 2024 criado', '127.0.0.1'),
('Sorteio Realizado', 'Sorteio de Inauguração realizado - Vencedor: Pedro Oliveira (Nº 3)', '127.0.0.1');

-- Verificar os dados inseridos
SELECT 'Participantes inseridos:' as info, COUNT(*) as total FROM participantes
UNION ALL
SELECT 'Com número da sorte:', COUNT(*) FROM participantes WHERE numero_da_sorte IS NOT NULL
UNION ALL
SELECT 'Sorteios criados:', COUNT(*) FROM sorteios
UNION ALL
SELECT 'Logs registrados:', COUNT(*) FROM admin_logs
UNION ALL
SELECT 'Usuários criados:', COUNT(*) FROM usuarios;

-- Mostrar distribuição por estado
SELECT estado, COUNT(*) as participantes 
FROM participantes 
WHERE estado IS NOT NULL 
GROUP BY estado 
ORDER BY participantes DESC;

-- Mostrar próximos sorteios
SELECT titulo, data_sorteio, status, premio 
FROM sorteios 
WHERE status = 'agendado' 
ORDER BY data_sorteio;

-- Informações importantes para teste
SELECT 
    'Para testar o sistema:' as dica,
    '1. Acesse /admin com senha: admin123' as passo1,
    '2. Gere números da sorte para participantes restantes' as passo2,
    '3. Realize um sorteio na aba Sorteios' as passo3,
    '4. Consulte números na área pública com emails de teste' as passo4;