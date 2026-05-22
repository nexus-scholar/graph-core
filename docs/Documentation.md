# Documentation & API Reference for `nexus-scholar/graph-core`

Welcome to the official documentation for `nexus-scholar/graph-core`. This guide provides everything you need to know to use the library effectively, from installation and basic usage to a detailed API reference.

-----

## 🚀 Getting Started

### Installation

Install the package via Composer:

```bash
composer require nexus-scholar/graph-core
```

### Quick Example

This example demonstrates the core functionality: creating a graph, adding nodes and edges with attributes, and exporting the result.

```php
<?php

require 'vendor/autoload.php';

use Mbsoft\Graph\Domain\Graph;
use Mbsoft\Graph\IO\CytoscapeJsonExporter;

// 1. Create a new directed graph instance
$graph = new Graph(directed: true);

// 2. Add nodes with attributes
$graph->addNode('A', ['label' => 'User A', 'type' => 'Person']);
$graph->addNode('B', ['label' => 'Project B', 'type' => 'Project']);

// 3. Add a directed edge with a 'weight' attribute
$graph->addEdge('A', 'B', ['relationship' => 'manages', 'weight' => 5]);

// 4. Check for nodes and edges
echo "Graph has node A: " . ($graph->hasNode('A') ? 'Yes' : 'No') . "\n"; // Yes
echo "Graph has edge from A to B: " . ($graph->hasEdge('A', 'B') ? 'Yes' : 'No') . "\n"; // Yes
echo "Graph has edge from B to A: " . ($graph->hasEdge('B', 'A') ? 'Yes' : 'No') . "\n"; // No

// 5. Get successors of a node
$successorsOfA = $graph->successors('A');
echo "Successors of A: " . implode(', ', $successorsOfA) . "\n"; // Successors of A: B

// 6. Export the graph to Cytoscape.js JSON format
$exporter = new CytoscapeJsonExporter();
$jsonOutput = $exporter->export($graph);

echo json_encode($jsonOutput, JSON_PRETTY_PRINT);
```

-----

## 💡 Core Concepts

### The `Graph` Class

The main class you'll interact with is `Mbsoft\Graph\Domain\Graph`. It's a mutable, high-performance graph implementation. Internally, it uses integer indices for nodes to ensure fast lookups, while exposing a user-friendly API based on string identifiers.

### Directed vs. Undirected Graphs

You can specify whether a graph is directed or undirected upon creation. This affects how edges are treated.

* **Directed (default):** An edge from `A` to `B` is distinct from an edge from `B` to `A`. `successors()` and `predecessors()` will differ.
* **Undirected:** An edge between `A` and `B` works in both directions. `successors()` and `predecessors()` of a node will return the same list of neighbors.

<!-- end list -->

```php
// A directed graph (default)
$directedGraph = new Graph();

// An undirected graph
$undirectedGraph = new Graph(directed: false);
```

### Nodes, Edges, and Attributes

* **Nodes:** Represent entities in the graph. Each node has a unique string ID.
* **Edges:** Represent connections between nodes.
* **Attributes:** Both nodes and edges can hold a key-value array of metadata, such as labels, weights, or types.

### `SubgraphView`

A `SubgraphView` provides a read-only, filtered "window" into an existing graph. It's a memory-efficient way to work with a subset of nodes and their interconnecting edges without duplicating any data.

-----

## 📚 API Reference

This section provides a detailed breakdown of all public interfaces, classes, and methods.

### `Mbsoft\Graph\Contracts`

#### **`GraphInterface`**

The primary read-only interface for any graph implementation.

| Method | Description |
| :--- | :--- |
| `isDirected(): bool` | Returns `true` if the graph is directed. |
| `nodes(): list<string>` | Returns a list of all node IDs. |
| `edges(): list<Edge>` | Returns a list of all `Edge` objects in the graph. |
| `successors(string $id): list<string>` | Gets the direct successors (out-neighbors) of a node. |
| `predecessors(string $id): list<string>` | Gets the direct predecessors (in-neighbors) of a node. |
| `hasNode(string $id): bool` | Checks if a node with the given ID exists. |
| `hasEdge(string $u, string $v): bool` | Checks if a direct edge exists from node `$u` to node `$v`. |
| `nodeAttrs(string $id): array` | Gets a copy of the attributes for a given node. |
| `edgeAttrs(string $u, string $v): array` | Gets a copy of the attributes for the edge from `$u` to `$v`. |

#### **`MutableGraphInterface`**

Extends `GraphInterface` with methods to modify the graph.

| Method | Description |
| :--- | :--- |
| `addNode(string $id, array $attrs = []): void` | Adds a node. If it exists, merges attributes. |
| `addEdge(string $u, string $v, array $attrs = []): void` | Adds an edge. Creates nodes if they don't exist. |
| `setNodeAttrs(string $id, array $attrs): void` | Overwrites all attributes for a given node. |
| `setEdgeAttrs(string $u, string $v, array $attrs): void` | Overwrites all attributes for a given edge. |

### `Mbsoft\Graph\Domain`

#### **`Graph`**

The main, concrete implementation of `MutableGraphInterface`.

* `__construct(bool $directed = true)`
  Creates a new graph instance.

* `static fromEdgeList(array $edges, bool $directed = true): self`
  A factory method to create a graph from a list of edges. Each edge in the array should be `['sourceId', 'targetId', ?array $attributes]`.

  ```php
  $graph = Graph::fromEdgeList([
      ['A', 'B', ['weight' => 1]],
      ['B', 'C'],
  ]);
  ```

#### **`Node`**

A `readonly` Data Transfer Object representing a node.

* `public string $id`
* `public array $attributes`

#### **`Edge`**

A `readonly` Data Transfer Object representing an edge.

* `public string $from` (Source node ID)
* `public string $to` (Target node ID)
* `public array $attributes`

#### **`SubgraphView`**

Creates a read-only view into a subset of a graph.

* `__construct(GraphInterface $originalGraph, list<string> $nodeIds)`
  The constructor takes the original graph and an array of node IDs to include in the view.

  ```php
  $fullGraph = Graph::fromEdgeList([['A','B'], ['B','C'], ['C','D']]);
  $subgraph = new SubgraphView($fullGraph, ['A', 'B', 'C']);

  $subgraph->hasNode('D'); // false
  $subgraph->edges(); // Returns only the A->B and B->C edges
  ```

### `Mbsoft\Graph\IO`

#### **`CytoscapeJsonExporter`**

Exports the graph to a JSON-serializable array compatible with [Cytoscape.js](https://js.cytoscape.org/).

* `export(GraphInterface $g): array`

  ```php
  $exporter = new CytoscapeJsonExporter();
  $data = $exporter->export($graph);
  // file_put_contents('graph.json', json_encode($data));
  ```

#### **`GraphMLExporter`**

Exports the graph to a GraphML string, a standard XML format for graphs.

* `export(GraphInterface $g): string`

  ```php
  $exporter = new GraphMLExporter();
  $xmlString = $exporter->export($graph);
  // file_put_contents('graph.graphml', $xmlString);
  ```

#### **`GexfExporter`**

Exports the graph to a GEXF string, another common XML-based graph format.

* `export(GraphInterface $g): string`

  ```php
  $exporter = new GexfExporter();
  $xmlString = $exporter->export($graph);
  // file_put_contents('graph.gexf', $xmlString);
  ```
