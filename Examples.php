<?php

/**
 * Example usage of nexus-scholar/graph-core library
 *
 * This file demonstrates various features of the graph library including:
 * - Creating directed and undirected graphs
 * - Adding nodes and edges with attributes
 * - Querying graph structure
 * - Creating subgraph views
 * - Exporting to different formats
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Mbsoft\Graph\Domain\Graph;
use Mbsoft\Graph\Domain\SubgraphView;
use Mbsoft\Graph\IO\CytoscapeJsonExporter;
use Mbsoft\Graph\IO\GraphMLExporter;
use Mbsoft\Graph\IO\GexfExporter;

echo "=== nexus-scholar/graph-core Demo ===\n\n";

// -----------------------------------------------------------------------------
// Example 1: Basic Graph Operations
// -----------------------------------------------------------------------------
echo "Example 1: Basic Graph Operations\n";
echo "---------------------------------\n";

$graph = new Graph(directed: true);

// Add nodes with attributes
$graph->addNode('Alice', ['age' => 30, 'city' => 'New York']);
$graph->addNode('Bob', ['age' => 25, 'city' => 'Boston']);
$graph->addNode('Charlie', ['age' => 35, 'city' => 'Chicago']);
$graph->addNode('Diana', ['age' => 28, 'city' => 'Denver']);

// Add edges with weights
$graph->addEdge('Alice', 'Bob', ['weight' => 1.0, 'relationship' => 'friend']);
$graph->addEdge('Bob', 'Charlie', ['weight' => 2.0, 'relationship' => 'colleague']);
$graph->addEdge('Charlie', 'Diana', ['weight' => 1.5, 'relationship' => 'friend']);
$graph->addEdge('Diana', 'Alice', ['weight' => 3.0, 'relationship' => 'family']);
$graph->addEdge('Alice', 'Charlie', ['weight' => 2.5, 'relationship' => 'colleague']);

echo "Graph created with " . count($graph->nodes()) . " nodes and " . count($graph->edges()) . " edges\n";
echo "\nNodes in the graph:\n";
foreach ($graph->nodes() as $node) {
    $attrs = $graph->nodeAttrs($node);
    echo "  - $node: age={$attrs['age']}, city={$attrs['city']}\n";
}

echo "\nEdges in the graph:\n";
foreach ($graph->edges() as $edge) {
    $weight = $edge->attributes['weight'] ?? 'N/A';
    $relationship = $edge->attributes['relationship'] ?? 'unknown';
    echo "  - {$edge->from} -> {$edge->to}: weight=$weight, type=$relationship\n";
}

echo "\nAlice's connections:\n";
echo "  Successors (outgoing): " . implode(', ', $graph->successors('Alice')) . "\n";
echo "  Predecessors (incoming): " . implode(', ', $graph->predecessors('Alice')) . "\n";

// -----------------------------------------------------------------------------
// Example 2: Undirected Graph (Social Network)
// -----------------------------------------------------------------------------
echo "\n\nExample 2: Undirected Social Network\n";
echo "------------------------------------\n";

$social = new Graph(directed: false);

// Add people
$people = ['Emma', 'Frank', 'Grace', 'Henry', 'Iris'];
foreach ($people as $person) {
    $social->addNode($person, ['joined' => rand(2018, 2024)]);
}

// Add friendships
$friendships = [
    ['Emma', 'Frank'],
    ['Frank', 'Grace'],
    ['Grace', 'Henry'],
    ['Henry', 'Iris'],
    ['Iris', 'Emma'],
    ['Emma', 'Grace'],
];

foreach ($friendships as [$person1, $person2]) {
    $social->addEdge($person1, $person2, ['since' => rand(2019, 2024)]);
}

echo "Social network with " . count($social->nodes()) . " people and " . count($social->edges()) . " friendships\n";

// Find the most connected person
$connections = [];
foreach ($social->nodes() as $person) {
    $connections[$person] = count($social->successors($person));
}
arsort($connections);
$mostConnected = array_key_first($connections);
echo "Most connected person: $mostConnected with {$connections[$mostConnected]} friends\n";

echo "\nFriend connections:\n";
foreach ($social->nodes() as $person) {
    $friends = $social->successors($person);
    echo "  $person is friends with: " . implode(', ', $friends) . "\n";
}

// -----------------------------------------------------------------------------
// Example 3: Subgraph Views
// -----------------------------------------------------------------------------
echo "\n\nExample 3: Subgraph Views\n";
echo "-------------------------\n";

// Create a larger graph
$fullGraph = new Graph(directed: true);

// Create a small company org chart
$employees = [
    'CEO' => ['level' => 1, 'department' => 'Executive'],
    'CTO' => ['level' => 2, 'department' => 'Technology'],
    'CFO' => ['level' => 2, 'department' => 'Finance'],
    'VP_Eng' => ['level' => 3, 'department' => 'Technology'],
    'VP_Product' => ['level' => 3, 'department' => 'Technology'],
    'Dev1' => ['level' => 4, 'department' => 'Technology'],
    'Dev2' => ['level' => 4, 'department' => 'Technology'],
    'Accountant' => ['level' => 3, 'department' => 'Finance'],
];

foreach ($employees as $name => $attrs) {
    $fullGraph->addNode($name, $attrs);
}

// Add reporting relationships
$reportingStructure = [
    ['CTO', 'CEO'],
    ['CFO', 'CEO'],
    ['VP_Eng', 'CTO'],
    ['VP_Product', 'CTO'],
    ['Dev1', 'VP_Eng'],
    ['Dev2', 'VP_Eng'],
    ['Accountant', 'CFO'],
];

foreach ($reportingStructure as [$employee, $manager]) {
    $fullGraph->addEdge($manager, $employee, ['type' => 'manages']);
}

echo "Full organization has " . count($fullGraph->nodes()) . " employees\n";

// Create a view of just the technology department
$techEmployees = array_keys(array_filter($employees, fn($attrs) => $attrs['department'] === 'Technology'));
$techDeptView = new SubgraphView($fullGraph, $techEmployees);

echo "Technology department view has " . count($techDeptView->nodes()) . " employees\n";
echo "Tech department members: " . implode(', ', $techDeptView->nodes()) . "\n";
echo "Reporting relationships in tech:\n";
foreach ($techDeptView->edges() as $edge) {
    echo "  {$edge->from} manages {$edge->to}\n";
}

// -----------------------------------------------------------------------------
// Example 4: Export Formats
// -----------------------------------------------------------------------------
echo "\n\nExample 4: Export Formats\n";
echo "-------------------------\n";

// Create a small graph for export demo
$exportGraph = Graph::fromEdgeList([
    ['Node1', 'Node2', ['weight' => 1.0]],
    ['Node2', 'Node3', ['weight' => 2.0]],
    ['Node3', 'Node4', ['weight' => 1.5]],
    ['Node4', 'Node1', ['weight' => 2.5]],
]);

// Add some node attributes
$exportGraph->setNodeAttrs('Node1', ['color' => 'red', 'size' => 10]);
$exportGraph->setNodeAttrs('Node2', ['color' => 'blue', 'size' => 15]);
$exportGraph->setNodeAttrs('Node3', ['color' => 'green', 'size' => 12]);
$exportGraph->setNodeAttrs('Node4', ['color' => 'yellow', 'size' => 8]);

// Export to Cytoscape JSON
$cytoscapeExporter = new CytoscapeJsonExporter();
$cytoscapeJson = $cytoscapeExporter->export($exportGraph);
echo "Cytoscape JSON export:\n";
echo "  - " . count($cytoscapeJson['elements']['nodes']) . " nodes\n";
echo "  - " . count($cytoscapeJson['elements']['edges']) . " edges\n";

// Export to GraphML
$graphMLExporter = new GraphMLExporter();
$graphML = $graphMLExporter->export($exportGraph);
echo "GraphML export:\n";
echo "  - " . strlen($graphML) . " bytes of XML\n";
echo "  - Contains " . substr_count($graphML, '<node') . " nodes\n";
echo "  - Contains " . substr_count($graphML, '<edge') . " edges\n";

// Export to GEXF
$gexfExporter = new GexfExporter();
$gexf = $gexfExporter->export($exportGraph);
echo "GEXF export:\n";
echo "  - " . strlen($gexf) . " bytes of XML\n";
echo "  - Format compatible with Gephi\n";

// -----------------------------------------------------------------------------
// Example 5: Performance Test
// -----------------------------------------------------------------------------
echo "\n\nExample 5: Performance Test\n";
echo "---------------------------\n";

$startTime = microtime(true);

// Create a larger graph
$perfGraph = new Graph();
$nodeCount = 1000;
$edgeCount = 5000;

// Add nodes
for ($i = 0; $i < $nodeCount; $i++) {
    $perfGraph->addNode("node_$i", ['index' => $i]);
}

// Add random edges
for ($i = 0; $i < $edgeCount; $i++) {
    $from = "node_" . rand(0, $nodeCount - 1);
    $to = "node_" . rand(0, $nodeCount - 1);
    if ($from !== $to) {
        $perfGraph->addEdge($from, $to, ['weight' => rand(1, 100) / 10]);
    }
}

$creationTime = microtime(true) - $startTime;

// Test various operations
$testNode = "node_500";

$startTime = microtime(true);
$hasNode = $perfGraph->hasNode($testNode);
$nodeCheckTime = microtime(true) - $startTime;

$startTime = microtime(true);
$successors = $perfGraph->successors($testNode);
$successorTime = microtime(true) - $startTime;

$startTime = microtime(true);
$predecessors = $perfGraph->predecessors($testNode);
$predecessorTime = microtime(true) - $startTime;

echo "Performance metrics for graph with $nodeCount nodes and ~$edgeCount edges:\n";
echo "  - Graph creation: " . number_format($creationTime * 1000, 2) . "ms\n";
echo "  - Node existence check: " . number_format($nodeCheckTime * 1000, 4) . "ms\n";
echo "  - Get successors: " . number_format($successorTime * 1000, 4) . "ms\n";
echo "  - Get predecessors: " . number_format($predecessorTime * 1000, 4) . "ms\n";
echo "  - Node $testNode has " . count($successors) . " successors and " . count($predecessors) . " predecessors\n";

// -----------------------------------------------------------------------------
// Summary
// -----------------------------------------------------------------------------
echo "\n\n=== Demo Complete ===\n";
echo "This demo showcased:\n";
echo "  ✅ Creating directed and undirected graphs\n";
echo "  ✅ Adding nodes and edges with attributes\n";
echo "  ✅ Querying graph structure (successors, predecessors)\n";
echo "  ✅ Creating efficient subgraph views\n";
echo "  ✅ Exporting to multiple formats (Cytoscape, GraphML, GEXF)\n";
echo "  ✅ Performance with large graphs (1000+ nodes)\n";
echo "\nFor more information, see the README.md file.\n";
