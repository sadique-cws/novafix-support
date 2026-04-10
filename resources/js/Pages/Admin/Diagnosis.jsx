import React, { useEffect, useMemo, useRef, useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { hierarchy, linkHorizontal, select, tree as d3Tree, zoom, zoomIdentity } from 'd3';

function escapeRegExp(str) {
    return String(str).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function highlightText(text, term) {
    const raw = String(text ?? '');
    const q = String(term ?? '').trim();
    if (!q) return raw;
    const parts = raw.split(new RegExp(`(${escapeRegExp(q)})`, 'ig'));
    if (parts.length === 1) return raw;
    return parts.map((p, i) =>
        p.toLowerCase() === q.toLowerCase() ? (
            <mark key={i} className="rounded bg-yellow-100 px-1 text-gray-900">
                {p}
            </mark>
        ) : (
            <React.Fragment key={i}>{p}</React.Fragment>
        )
    );
}

function TreeNode({
    nodeId,
    nodes,
    depth = 0,
    expanded,
    toggle,
    selectedId,
    onSelect,
    visibleSet,
    searchTerm,
    compactMode,
}) {
    const node = nodes?.[nodeId];
    if (!node) return null;

    const hasChildren = Boolean(node.yes || node.no);
    const isExpanded = expanded[nodeId] ?? depth < 2;
    const isSelected = String(selectedId) === String(nodeId);

    return (
        <div className={compactMode ? 'mt-1.5' : 'mt-2'}>
            <div
                className={`flex items-start gap-2 rounded-lg border bg-white cursor-pointer ${
                    isSelected ? 'border-indigo-400 ring-2 ring-indigo-100' : 'border-gray-200 hover:border-gray-300'
                } ${compactMode ? 'px-2.5 py-2 shadow-none' : 'p-3 shadow-sm'}`}
                style={{ marginLeft: depth * (compactMode ? 12 : 16) }}
                role="button"
                tabIndex={0}
                onClick={() => onSelect?.(nodeId)}
                onKeyDown={(e) => {
                    if (e.key === 'Enter' || e.key === ' ') onSelect?.(nodeId);
                }}
            >
                {hasChildren ? (
                    <button
                        type="button"
                        onClick={(e) => {
                            e.stopPropagation();
                            toggle(nodeId);
                        }}
                        className={`mt-0.5 inline-flex items-center justify-center rounded border border-gray-300 text-gray-700 hover:bg-gray-50 ${
                            compactMode ? 'h-7 w-7' : 'h-6 w-6'
                        }`}
                        aria-label="Toggle node"
                    >
                        {isExpanded ? '−' : '+'}
                    </button>
                ) : (
                    <span className={`mt-1 inline-block ${compactMode ? 'h-7 w-7' : 'h-6 w-6'}`} />
                )}
                <div className="min-w-0 flex-1">
                    {compactMode ? (
                        <>
                            <div className="flex items-baseline gap-2">
                                <div className="text-[11px] font-semibold text-gray-500">#{node.id}</div>
                                <div className="min-w-0 flex-1 text-sm font-semibold leading-snug text-gray-900 truncate">
                                    {highlightText(node.text, searchTerm)}
                                </div>
                            </div>
                            <div className="mt-1 flex flex-wrap gap-1.5 text-[11px]">
                                <span className="rounded bg-green-50 px-2 py-1 text-green-700">Y → {node.yes ?? 'END'}</span>
                                <span className="rounded bg-red-50 px-2 py-1 text-red-700">N → {node.no ?? 'END'}</span>
                            </div>
                        </>
                    ) : (
                        <>
                            <div className="text-xs text-gray-500">#{node.id}</div>
                            <div className="mt-0.5 text-sm font-semibold leading-snug text-gray-900 break-words">
                                {highlightText(node.text, searchTerm)}
                            </div>
                            <div className="mt-2 flex flex-wrap gap-2 text-xs">
                                <span className="rounded-md bg-green-50 px-2 py-1 text-green-700">
                                    YES → {node.yes ?? 'END'}
                                </span>
                                <span className="rounded-md bg-red-50 px-2 py-1 text-red-700">NO → {node.no ?? 'END'}</span>
                            </div>
                        </>
                    )}
                </div>
            </div>

            {hasChildren && isExpanded ? (
                <div className={compactMode ? 'mt-1.5' : 'mt-2'}>
                    {node.yes && (!visibleSet || visibleSet.has(node.yes)) ? (
                        <div>
                            <div
                                className="text-xs font-semibold text-green-700"
                                style={{ marginLeft: (depth + 1) * (compactMode ? 12 : 16) }}
                            >
                                YES
                            </div>
                            <TreeNode
                                nodeId={node.yes}
                                nodes={nodes}
                                depth={depth + 1}
                                expanded={expanded}
                                toggle={toggle}
                                selectedId={selectedId}
                                onSelect={onSelect}
                                visibleSet={visibleSet}
                                searchTerm={searchTerm}
                                compactMode={compactMode}
                            />
                        </div>
                    ) : null}
                    {node.no && (!visibleSet || visibleSet.has(node.no)) ? (
                        <div className="mt-2">
                            <div
                                className="text-xs font-semibold text-red-700"
                                style={{ marginLeft: (depth + 1) * (compactMode ? 12 : 16) }}
                            >
                                NO
                            </div>
                            <TreeNode
                                nodeId={node.no}
                                nodes={nodes}
                                depth={depth + 1}
                                expanded={expanded}
                                toggle={toggle}
                                selectedId={selectedId}
                                onSelect={onSelect}
                                visibleSet={visibleSet}
                                searchTerm={searchTerm}
                                compactMode={compactMode}
                            />
                        </div>
                    ) : null}
                </div>
            ) : null}
        </div>
    );
}

function D3TreeGraph({ roots, nodes, selectedId, onSelect, onNavigateBranch, searchTerm }) {
    const wrapperRef = useRef(null);
    const svgRef = useRef(null);
    const zoomBehaviorRef = useRef(null);
    const contentBoundsRef = useRef({ width: 0, height: 0 });
    const currentTransformRef = useRef(zoomIdentity);
    const nodeCoordsRef = useRef({});

    const graphData = useMemo(() => {
        if (!nodes || !roots?.length) return null;

        const buildNode = (id, lineage = new Set(), branch = null) => {
            const node = nodes?.[id];
            if (!node) return null;

            if (lineage.has(id)) {
                return {
                    id,
                    text: `#${id} (cycle)`,
                    branch,
                    children: [],
                };
            }

            const nextLineage = new Set(lineage);
            nextLineage.add(id);
            const children = [];

            if (node.yes) {
                const yes = buildNode(Number(node.yes), nextLineage, 'YES');
                if (yes) children.push(yes);
            }
            if (node.no) {
                const no = buildNode(Number(node.no), nextLineage, 'NO');
                if (no) children.push(no);
            }

            return {
                id: node.id,
                text: node.text ?? '',
                branch,
                children,
            };
        };

        const children = roots.map((id) => buildNode(Number(id))).filter(Boolean);
        if (!children.length) return null;

        return { id: 'root', text: 'Root', children };
    }, [roots, nodes]);

    const centerOnNode = (nodeId, targetScale = null) => {
        const svgEl = svgRef.current;
        const wrapEl = wrapperRef.current;
        const behavior = zoomBehaviorRef.current;
        const coords = nodeCoordsRef.current[String(nodeId)];
        if (!svgEl || !wrapEl || !behavior || !coords) return;

        const current = currentTransformRef.current || zoomIdentity;
        const nextScale = targetScale || current.k || 1;
        const tx = wrapEl.clientWidth / 2 - coords.absX * nextScale;
        const ty = wrapEl.clientHeight / 2 - coords.absY * nextScale;
        const transform = zoomIdentity.translate(tx, ty).scale(nextScale);
        select(svgEl).transition().duration(220).call(behavior.transform, transform);
    };

    useEffect(() => {
        const svgEl = svgRef.current;
        const wrapEl = wrapperRef.current;
        if (!svgEl || !wrapEl) return;

        const svg = select(svgEl);
        svg.selectAll('*').remove();

        if (!graphData?.children?.length) return;

        const root = hierarchy(graphData);
        d3Tree().nodeSize([56, 240])(root);

        const points = root.descendants().filter((d) => d.data.id !== 'root');
        if (!points.length) return;

        const minX = Math.min(...points.map((d) => d.x));
        const maxX = Math.max(...points.map((d) => d.x));
        const levels = Math.max(...points.map((d) => d.depth));

        const containerWidth = Math.max(900, wrapEl.clientWidth || 900);
        const width = Math.max(containerWidth, levels * 240 + 460);
        const height = Math.max(380, maxX - minX + 140);
        const topPad = 50 - minX;
        const leftPad = 70;

        svg.attr('viewBox', `0 0 ${width} ${height}`);
        svg.attr('preserveAspectRatio', 'xMinYMin meet');

        const viewport = svg.append('g').attr('class', 'zoom-viewport');
        const g = viewport.append('g').attr('transform', `translate(${leftPad},${topPad})`);

        contentBoundsRef.current = { width, height };

        const fitScale = Math.max(0.25, Math.min(1, (wrapEl.clientWidth - 24) / width));
        const initialTransform = zoomIdentity.translate(10, 10).scale(fitScale);

        const behavior = zoom()
            .scaleExtent([0.25, 2.5])
            .filter((event) => {
                if (event.type === 'wheel') return true;
                return !event.button || event.button === 0;
            })
            .on('zoom', (event) => {
                currentTransformRef.current = event.transform;
                viewport.attr('transform', event.transform);
            });

        zoomBehaviorRef.current = behavior;
        svg.call(behavior);
        svg.call(behavior.transform, initialTransform);
        svg.on('dblclick.zoom', null);

        g.append('g')
            .selectAll('path')
            .data(root.links().filter((link) => link.source.data.id !== 'root'))
            .join('path')
            .attr('fill', 'none')
            .attr('stroke-width', 2)
            .attr('stroke', (d) => (d.target.data.branch === 'YES' ? '#16a34a' : '#dc2626'))
            .attr(
                'd',
                linkHorizontal()
                    .x((d) => d.y)
                    .y((d) => d.x)
            );

        const q = String(searchTerm || '').trim().toLowerCase();

        const coords = {};
        points.forEach((d) => {
            coords[String(d.data.id)] = {
                absX: leftPad + d.y,
                absY: topPad + d.x,
            };
        });
        nodeCoordsRef.current = coords;

        const nodesG = g.append('g').selectAll('g').data(points).join('g').attr('transform', (d) => `translate(${d.y},${d.x})`);

        nodesG
            .append('circle')
            .attr('r', 8)
            .attr('cursor', 'pointer')
            .attr('fill', (d) => {
                const isSelected = String(selectedId) === String(d.data.id);
                if (isSelected) return '#2563eb';
                const hit = q && `${d.data.id} ${d.data.text}`.toLowerCase().includes(q);
                return hit ? '#f59e0b' : '#ffffff';
            })
            .attr('stroke', (d) => (String(selectedId) === String(d.data.id) ? '#1d4ed8' : '#475569'))
            .attr('stroke-width', (d) => (String(selectedId) === String(d.data.id) ? 2.5 : 1.5))
            .on('click', (_, d) => onSelect?.(String(d.data.id)));

        nodesG.on('dblclick', (_, d) => {
            const nodeId = String(d.data.id);
            onSelect?.(nodeId);
            centerOnNode(nodeId, 1.6);
        });

        nodesG
            .append('text')
            .attr('x', 14)
            .attr('y', -3)
            .style('font-size', '12px')
            .style('font-weight', '700')
            .style('fill', '#334155')
            .text((d) => `#${d.data.id}`);

        nodesG
            .append('text')
            .attr('x', 14)
            .attr('y', 14)
            .style('font-size', '12px')
            .style('fill', '#0f172a')
            .text((d) => {
                const text = String(d.data.text || '');
                return text.length > 44 ? `${text.slice(0, 44)}...` : text;
            });

        const branchItems = [];
        points.forEach((d) => {
            const modelNode = nodes?.[d.data.id];
            if (!modelNode) return;
            if (modelNode.yes) {
                branchItems.push({
                    key: `${d.data.id}-yes`,
                    targetId: String(modelNode.yes),
                    label: 'YES',
                    x: d.y + 124,
                    y: d.x - 14,
                    bg: '#dcfce7',
                    text: '#15803d',
                });
            }
            if (modelNode.no) {
                branchItems.push({
                    key: `${d.data.id}-no`,
                    targetId: String(modelNode.no),
                    label: 'NO',
                    x: d.y + 124,
                    y: d.x + 6,
                    bg: '#fee2e2',
                    text: '#b91c1c',
                });
            }
        });

        const branchG = g.append('g').selectAll('g').data(branchItems).join('g').attr('transform', (d) => `translate(${d.x},${d.y})`);

        branchG
            .append('rect')
            .attr('width', 44)
            .attr('height', 16)
            .attr('rx', 6)
            .attr('fill', (d) => d.bg)
            .attr('cursor', 'pointer')
            .on('click', (_, d) => {
                onNavigateBranch?.(d.targetId);
                centerOnNode(d.targetId, 1.45);
            });

        branchG
            .append('text')
            .attr('x', 22)
            .attr('y', 11.5)
            .attr('text-anchor', 'middle')
            .style('font-size', '10px')
            .style('font-weight', '700')
            .style('fill', (d) => d.text)
            .style('pointer-events', 'none')
            .text((d) => d.label);

        nodesG.append('title').text((d) => `#${d.data.id} ${d.data.text || ''}`);
    }, [graphData, selectedId, onSelect, onNavigateBranch, searchTerm, nodes]);

    const zoomByFactor = (factor) => {
        const svgEl = svgRef.current;
        const behavior = zoomBehaviorRef.current;
        if (!svgEl || !behavior) return;
        select(svgEl).transition().duration(180).call(behavior.scaleBy, factor);
    };

    const resetZoom = () => {
        const svgEl = svgRef.current;
        const wrapEl = wrapperRef.current;
        const behavior = zoomBehaviorRef.current;
        const { width } = contentBoundsRef.current;
        if (!svgEl || !wrapEl || !behavior || !width) return;
        const fitScale = Math.max(0.25, Math.min(1, (wrapEl.clientWidth - 24) / width));
        const transform = zoomIdentity.translate(10, 10).scale(fitScale);
        select(svgEl).transition().duration(220).call(behavior.transform, transform);
    };

    const zoomToSelected = () => {
        if (!selectedId) return;
        centerOnNode(String(selectedId), 1.55);
    };

    return (
        <div className="rounded-lg border border-gray-200 bg-white">
            <div className="flex items-center justify-between border-b border-gray-200 px-3 py-2">
                <div className="text-xs font-semibold uppercase tracking-wide text-gray-500">D3 Diagram</div>
                <div className="flex items-center gap-2">
                    <button
                        type="button"
                        onClick={() => zoomByFactor(1.2)}
                        className="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Zoom +
                    </button>
                    <button
                        type="button"
                        onClick={() => zoomByFactor(0.85)}
                        className="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Zoom -
                    </button>
                    <button
                        type="button"
                        onClick={zoomToSelected}
                        disabled={!selectedId}
                        className="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                    >
                        Zoom Node
                    </button>
                    <button
                        type="button"
                        onClick={resetZoom}
                        className="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Reset
                    </button>
                </div>
            </div>
            <div ref={wrapperRef} className="max-h-[70vh] overflow-hidden">
                <svg ref={svgRef} className="h-[72vh] min-h-[380px] w-full cursor-grab active:cursor-grabbing" />
            </div>
        </div>
    );
}

export default function Diagnosis({ devices, brands, models, problems }) {
    const { flash } = usePage().props;

    const [selectedDeviceId, setSelectedDeviceId] = useState('');
    const [selectedBrandId, setSelectedBrandId] = useState('');
    const [selectedModelId, setSelectedModelId] = useState('');
    const [selectedProblemId, setSelectedProblemId] = useState('');

    const [tree, setTree] = useState(null);
    const [treeLoading, setTreeLoading] = useState(false);
    const [expanded, setExpanded] = useState({});
    const [selectedNodeId, setSelectedNodeId] = useState('');
    const [searchTerm, setSearchTerm] = useState('');
    const [focusMode, setFocusMode] = useState(false);
    const [compactMode, setCompactMode] = useState(true);
    const [treeViewMode, setTreeViewMode] = useState('d3');

    const [sourceProblemId, setSourceProblemId] = useState('');
    const [sourceQuestions, setSourceQuestions] = useState([]);
    const [targetQuestions, setTargetQuestions] = useState([]);
    const [cloneSearch, setCloneSearch] = useState('');

    const filteredBrands = useMemo(
        () => brands.filter((b) => String(b.device_id) === String(selectedDeviceId)),
        [brands, selectedDeviceId]
    );
    const filteredModels = useMemo(
        () => models.filter((m) => String(m.brand_id) === String(selectedBrandId)),
        [models, selectedBrandId]
    );
    const filteredProblems = useMemo(
        () => problems.filter((p) => String(p.model_id) === String(selectedModelId)),
        [problems, selectedModelId]
    );

    const cloneForm = useForm({
        source_problem_id: '',
        source_question_id: '',
        target_problem_id: '',
        attach_mode: 'yes',
        target_attach_question_id: '',
    });

    const editForm = useForm({
        question_text: '',
    });

    const rootForm = useForm({
        problem_id: '',
        question_text: '',
    });

    const yesChildForm = useForm({
        parent_question_id: '',
        attach_mode: 'yes',
        question_text: '',
    });

    const noChildForm = useForm({
        parent_question_id: '',
        attach_mode: 'no',
        question_text: '',
    });

    const toggle = (id) => setExpanded((prev) => ({ ...prev, [id]: !(prev[id] ?? false) }));

    const expandAll = () => {
        if (!tree?.nodes) return;
        const next = {};
        Object.values(tree.nodes).forEach((n) => {
            if (n?.yes || n?.no) next[n.id] = true;
        });
        setExpanded(next);
    };

    const collapseAll = () => setExpanded({});

    const refreshTarget = () => {
        if (!selectedProblemId) return;
        setTreeLoading(true);
        Promise.all([
            fetch(`/admin/diagnosis/tree/${selectedProblemId}`).then((r) => r.json()),
            fetch(`/admin/diagnosis/questions/${selectedProblemId}`).then((r) => r.json()),
        ])
            .then(([treeJson, questionsJson]) => {
                setTree(treeJson);
                setTargetQuestions(questionsJson.questions || []);
            })
            .finally(() => setTreeLoading(false));
    };

    const parentMap = useMemo(() => {
        const map = {};
        if (!tree?.nodes) return map;
        Object.values(tree.nodes).forEach((n) => {
            if (!n) return;
            if (n.yes) {
                map[n.yes] = map[n.yes] || [];
                map[n.yes].push(n.id);
            }
            if (n.no) {
                map[n.no] = map[n.no] || [];
                map[n.no].push(n.id);
            }
        });
        return map;
    }, [tree]);

    const selectedNode = tree?.nodes?.[selectedNodeId] ?? null;

    const visibleSet = useMemo(() => {
        if (!focusMode || !tree?.nodes || !selectedNodeId) return null;
        const visible = new Set();
        const queue = [Number(selectedNodeId)];

        // Descendants from selected
        while (queue.length) {
            const id = queue.shift();
            if (!id || visible.has(id)) continue;
            visible.add(id);
            const n = tree.nodes[id];
            if (!n) continue;
            if (n.yes) queue.push(n.yes);
            if (n.no) queue.push(n.no);
        }

        // Ancestors of selected (handle multiple parents)
        const anc = [Number(selectedNodeId)];
        while (anc.length) {
            const cur = anc.shift();
            const parents = parentMap[cur] || [];
            for (const p of parents) {
                if (!visible.has(p)) visible.add(p);
                anc.push(p);
            }
        }

        return visible;
    }, [focusMode, tree, selectedNodeId, parentMap]);

    const focusRoots = useMemo(() => {
        if (!focusMode || !visibleSet || !tree?.roots) return tree?.roots || [];
        return tree.roots.filter((r) => visibleSet.has(r));
    }, [focusMode, visibleSet, tree]);

    const searchResults = useMemo(() => {
        const q = String(searchTerm ?? '').trim().toLowerCase();
        if (!q || !tree?.nodes) return [];
        const results = [];
        for (const n of Object.values(tree.nodes)) {
            if (!n) continue;
            const hay = `${n.id} ${n.text ?? ''}`.toLowerCase();
            if (hay.includes(q)) results.push(n);
            if (results.length >= 25) break;
        }
        return results;
    }, [searchTerm, tree]);

    useEffect(() => {
        setSelectedBrandId('');
        setSelectedModelId('');
        setSelectedProblemId('');
        setTree(null);
        setTargetQuestions([]);
        setExpanded({});
        setSelectedNodeId('');
        setSearchTerm('');
        setFocusMode(false);
        setCompactMode(true);
        setTreeViewMode('d3');
    }, [selectedDeviceId]);

    useEffect(() => {
        setSelectedModelId('');
        setSelectedProblemId('');
        setTree(null);
        setTargetQuestions([]);
        setExpanded({});
        setSelectedNodeId('');
        setSearchTerm('');
        setFocusMode(false);
        setCompactMode(true);
        setTreeViewMode('d3');
    }, [selectedBrandId]);

    useEffect(() => {
        setSelectedProblemId('');
        setTree(null);
        setTargetQuestions([]);
        setExpanded({});
        setSelectedNodeId('');
        setSearchTerm('');
        setFocusMode(false);
        setCompactMode(true);
        setTreeViewMode('d3');
    }, [selectedModelId]);

    useEffect(() => {
        if (!selectedProblemId) return;

        setTreeLoading(true);
        Promise.all([
            fetch(`/admin/diagnosis/tree/${selectedProblemId}`).then((r) => r.json()),
            fetch(`/admin/diagnosis/questions/${selectedProblemId}`).then((r) => r.json()),
        ])
            .then(([treeJson, questionsJson]) => {
                setTree(treeJson);
                setTargetQuestions(questionsJson.questions || []);
                setExpanded({});
                cloneForm.setData('target_problem_id', String(selectedProblemId));
                rootForm.setData('problem_id', String(selectedProblemId));
                setSelectedNodeId(treeJson?.roots?.[0] ? String(treeJson.roots[0]) : '');
            })
            .finally(() => setTreeLoading(false));
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [selectedProblemId]);

    useEffect(() => {
        if (!sourceProblemId) {
            setSourceQuestions([]);
            cloneForm.setData('source_problem_id', '');
            cloneForm.setData('source_question_id', '');
            return;
        }

        fetch(`/admin/diagnosis/questions/${sourceProblemId}`)
            .then((r) => r.json())
            .then((json) => {
                setSourceQuestions(json.questions || []);
                cloneForm.setData('source_problem_id', String(sourceProblemId));
                cloneForm.setData('source_question_id', '');
            });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [sourceProblemId]);

    const canClone =
        cloneForm.data.source_problem_id &&
        cloneForm.data.source_question_id &&
        cloneForm.data.target_problem_id &&
        (cloneForm.data.attach_mode === 'root' || cloneForm.data.target_attach_question_id || selectedNodeId);

    const submitClone = (e) => {
        e.preventDefault();
        const effectiveTargetAttachId =
            cloneForm.data.attach_mode === 'root'
                ? ''
                : cloneForm.data.target_attach_question_id || selectedNodeId || '';

        cloneForm
            .transform((data) => ({
                ...data,
                target_attach_question_id: effectiveTargetAttachId,
            }))
            .post('/admin/diagnosis/clone', {
            preserveScroll: true,
            onSuccess: () => {
                refreshTarget();
            },
            onFinish: () => {
                cloneForm.transform((data) => data);
            },
        });
    };

    const useSelectedAsAttachTarget = () => {
        if (!selectedNodeId || cloneForm.data.attach_mode === 'root') return;
        cloneForm.setData('target_attach_question_id', String(selectedNodeId));
    };

    useEffect(() => {
        if (!selectedNode) return;
        editForm.setData('question_text', selectedNode.text ?? '');
        yesChildForm.setData('parent_question_id', String(selectedNode.id));
        noChildForm.setData('parent_question_id', String(selectedNode.id));
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [selectedNodeId, tree?.nodes]);

    const submitEdit = (e) => {
        e.preventDefault();
        if (!selectedNodeId) return;
        editForm.put(`/admin/diagnosis/question/${selectedNodeId}`, {
            preserveScroll: true,
            onSuccess: () => refreshTarget(),
        });
    };

    const submitRoot = (e) => {
        e.preventDefault();
        rootForm.post('/admin/diagnosis/root', {
            preserveScroll: true,
            onSuccess: () => {
                rootForm.reset('question_text');
                refreshTarget();
            },
        });
    };

    const submitYesChild = (e) => {
        e.preventDefault();
        yesChildForm.post('/admin/diagnosis/branch', {
            preserveScroll: true,
            onSuccess: () => {
                yesChildForm.reset('question_text');
                refreshTarget();
            },
        });
    };

    const submitNoChild = (e) => {
        e.preventDefault();
        noChildForm.post('/admin/diagnosis/branch', {
            preserveScroll: true,
            onSuccess: () => {
                noChildForm.reset('question_text');
                refreshTarget();
            },
        });
    };

    const filteredSourceQuestions = useMemo(() => {
        const q = String(cloneSearch || '').trim().toLowerCase();
        if (!q) return sourceQuestions;
        return sourceQuestions.filter((x) => {
            const hay = `${x.id} ${x.text ?? ''}`.toLowerCase();
            return hay.includes(q);
        });
    }, [cloneSearch, sourceQuestions]);

    const filteredTargetQuestions = useMemo(() => {
        const q = String(cloneSearch || '').trim().toLowerCase();
        if (!q) return targetQuestions;
        return targetQuestions.filter((x) => {
            const hay = `${x.id} ${x.text ?? ''}`.toLowerCase();
            return hay.includes(q);
        });
    }, [cloneSearch, targetQuestions]);

    return (
        <div className="w-full">
            <Head title="Admin Diagnosis (React Tree)" />

            <div className="flex items-start justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-semibold text-gray-900">Admin Diagnosis</h1>
                    <p className="mt-1 text-sm text-gray-600">
                        Select a Problem to view its full Yes/No question tree and clone sub-flows to reuse them.
                    </p>
                </div>
            </div>

            {flash?.message ? (
                <div className="mt-4 rounded-lg border border-green-200 bg-green-50 p-3 text-green-800">{flash.message}</div>
            ) : null}
            {flash?.error ? (
                <div className="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-red-800">{flash.error}</div>
            ) : null}

            <div className="mt-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div className="text-sm font-semibold text-gray-800">Target Flow</div>
                    {selectedProblemId ? (
                        <div className="text-xs text-gray-500">
                            {tree?.count ? `${tree.count} questions` : null}
                        </div>
                    ) : null}
                </div>

                <div className="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label className="text-xs font-medium text-gray-700">Device</label>
                        <select
                            className="mt-1 w-full rounded-lg border border-gray-300 p-2"
                            value={selectedDeviceId}
                            onChange={(e) => setSelectedDeviceId(e.target.value)}
                        >
                            <option value="">Choose device</option>
                            {devices.map((d) => (
                                <option key={d.id} value={d.id}>
                                    {d.name}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label className="text-xs font-medium text-gray-700">Brand</label>
                        <select
                            className="mt-1 w-full rounded-lg border border-gray-300 p-2"
                            value={selectedBrandId}
                            onChange={(e) => setSelectedBrandId(e.target.value)}
                            disabled={!selectedDeviceId}
                        >
                            <option value="">Choose brand</option>
                            {filteredBrands.map((b) => (
                                <option key={b.id} value={b.id}>
                                    {b.name}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label className="text-xs font-medium text-gray-700">Model</label>
                        <select
                            className="mt-1 w-full rounded-lg border border-gray-300 p-2"
                            value={selectedModelId}
                            onChange={(e) => setSelectedModelId(e.target.value)}
                            disabled={!selectedBrandId}
                        >
                            <option value="">Choose model</option>
                            {filteredModels.map((m) => (
                                <option key={m.id} value={m.id}>
                                    {m.name}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label className="text-xs font-medium text-gray-700">Problem</label>
                        <select
                            className="mt-1 w-full rounded-lg border border-gray-300 p-2"
                            value={selectedProblemId}
                            onChange={(e) => setSelectedProblemId(e.target.value)}
                            disabled={!selectedModelId}
                        >
                            <option value="">Choose problem</option>
                            {filteredProblems.map((p) => (
                                <option key={p.id} value={p.id}>
                                    {p.name}
                                </option>
                            ))}
                        </select>
                    </div>
                </div>
            </div>

            <div className="mt-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex items-center justify-between">
                        <div className="text-sm font-semibold text-gray-800">Tree Viewer</div>
                        {selectedProblemId && focusMode ? (
                            <div className="text-xs text-gray-500">Focus view</div>
                        ) : null}
                    </div>

                    {selectedProblemId ? (
                        <div className="mt-3 flex flex-wrap items-center justify-between gap-3">
                            <div className="flex flex-wrap items-center gap-2">
                                <div className="mr-2 inline-flex overflow-hidden rounded-md border border-gray-300 bg-white">
                                    <button
                                        type="button"
                                        onClick={() => setTreeViewMode('d3')}
                                        className={`px-3 py-1.5 text-xs font-semibold ${
                                            treeViewMode === 'd3' ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-50'
                                        }`}
                                    >
                                        D3 Tree
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setTreeViewMode('card')}
                                        className={`border-l border-gray-300 px-3 py-1.5 text-xs font-semibold ${
                                            treeViewMode === 'card' ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-50'
                                        }`}
                                    >
                                        Card Tree
                                    </button>
                                </div>
                                <button
                                    type="button"
                                    onClick={expandAll}
                                    className="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Expand all
                                </button>
                                <button
                                    type="button"
                                    onClick={collapseAll}
                                    className="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Collapse all
                                </button>
                                <label className="flex items-center gap-2 text-xs text-gray-700">
                                    <input
                                        type="checkbox"
                                        className="rounded border-gray-300"
                                        checked={compactMode}
                                        onChange={(e) => setCompactMode(e.target.checked)}
                                    />
                                    Compact
                                </label>
                                <label className="flex items-center gap-2 text-xs text-gray-700">
                                    <input
                                        type="checkbox"
                                        className="rounded border-gray-300"
                                        checked={focusMode}
                                        onChange={(e) => setFocusMode(e.target.checked)}
                                        disabled={!selectedNodeId}
                                    />
                                    Focus selected
                                </label>
                            </div>

                            <div className="relative w-full sm:w-[360px]">
                                <input
                                    className="w-full rounded-lg border border-gray-300 p-2 text-sm"
                                    placeholder="Search by id or text…"
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                />
                                {searchResults.length ? (
                                    <div className="absolute z-10 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow">
                                        {searchResults.map((n) => (
                                            <button
                                                key={n.id}
                                                type="button"
                                                className="flex w-full items-start gap-2 px-3 py-2 text-left text-sm hover:bg-gray-50"
                                                onClick={() => {
                                                    setSelectedNodeId(String(n.id));
                                                    setSearchTerm('');
                                                    setFocusMode(true);
                                                }}
                                            >
                                                <div className="mt-0.5 text-xs font-semibold text-gray-700">#{n.id}</div>
                                                <div className="min-w-0 flex-1 text-xs text-gray-700">
                                                    <div className="truncate">{highlightText(n.text, String(searchTerm ?? '').trim())}</div>
                                                </div>
                                            </button>
                                        ))}
                                    </div>
                                ) : null}
                            </div>
                        </div>
                    ) : null}

                    {!selectedProblemId ? (
                        <div className="mt-4 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-6 text-sm text-gray-600">
                            Select a Target Problem to load the tree.
                        </div>
                    ) : treeLoading ? (
                        <div className="mt-4 text-sm text-gray-600">Loading tree…</div>
                    ) : tree?.roots?.length ? (
                        <div className="mt-4 lg:flex lg:items-start lg:gap-4">
                            <div className="lg:flex-1 lg:min-w-0">
                            {tree.roots.length > 1 ? (
                                <div className="mb-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                                    Multiple root questions detected ({tree.roots.length}). This usually means the flow has disconnected parts.
                                </div>
                            ) : null}
                            {treeViewMode === 'd3' ? (
                                <D3TreeGraph
                                    roots={focusRoots}
                                    nodes={tree.nodes}
                                    selectedId={selectedNodeId}
                                    onSelect={(id) => setSelectedNodeId(String(id))}
                                    onNavigateBranch={(nextId) => {
                                        setSelectedNodeId(String(nextId));
                                        setFocusMode(true);
                                    }}
                                    searchTerm={searchTerm}
                                />
                            ) : (
                                <div className="max-h-[70vh] overflow-auto pr-2">
                                    {focusRoots.map((rootId) => (
                                        <TreeNode
                                            key={rootId}
                                            nodeId={rootId}
                                            nodes={tree.nodes}
                                            expanded={expanded}
                                            toggle={toggle}
                                            selectedId={selectedNodeId}
                                            onSelect={(id) => setSelectedNodeId(String(id))}
                                            visibleSet={visibleSet}
                                            searchTerm={searchTerm}
                                            compactMode={compactMode}
                                        />
                                    ))}
                                </div>
                            )}
                            </div>

                            <div className="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-4 lg:mt-0 lg:w-[380px] lg:shrink-0 lg:sticky lg:top-20 max-h-[70vh] overflow-auto">
                                <div className="text-sm font-semibold text-gray-800">Selected Question</div>
                                {!selectedNode ? (
                                    <div className="mt-3 text-sm text-gray-600">Click any question node to edit or add children.</div>
                                ) : (
                                    <div className="mt-3 space-y-4">
                                        <div className="text-xs text-gray-600">#{selectedNode.id}</div>

                                        <form onSubmit={submitEdit} className="space-y-2">
                                            <label className="text-xs font-medium text-gray-700">Edit question text</label>
                                            <textarea
                                                className="w-full rounded-lg border border-gray-300 p-2 text-sm"
                                                rows={4}
                                                value={editForm.data.question_text}
                                                onChange={(e) => editForm.setData('question_text', e.target.value)}
                                            />
                                            <button
                                                type="submit"
                                                disabled={editForm.processing}
                                                className="w-full rounded-lg bg-gray-900 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-800 disabled:opacity-50"
                                            >
                                                Save
                                            </button>
                                        </form>

                                        <div className="border-t border-gray-200 pt-4">
                                            <div className="text-xs font-semibold text-gray-700">Add child questions</div>

                                            <form onSubmit={submitYesChild} className="mt-3 space-y-2">
                                                <div className="text-xs font-medium text-green-700">YES branch</div>
                                                {selectedNode.yes ? (
                                                    <div className="text-xs text-gray-600">Already set to #{selectedNode.yes}</div>
                                                ) : (
                                                    <>
                                                        <input
                                                            className="w-full rounded-lg border border-gray-300 p-2 text-sm"
                                                            placeholder="New YES question text"
                                                            value={yesChildForm.data.question_text}
                                                            onChange={(e) => yesChildForm.setData('question_text', e.target.value)}
                                                        />
                                                        <button
                                                            type="submit"
                                                            disabled={yesChildForm.processing || !yesChildForm.data.question_text}
                                                            className="w-full rounded-lg bg-green-600 px-3 py-2 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-50"
                                                        >
                                                            Create YES child
                                                        </button>
                                                    </>
                                                )}
                                            </form>

                                            <form onSubmit={submitNoChild} className="mt-4 space-y-2">
                                                <div className="text-xs font-medium text-red-700">NO branch</div>
                                                {selectedNode.no ? (
                                                    <div className="text-xs text-gray-600">Already set to #{selectedNode.no}</div>
                                                ) : (
                                                    <>
                                                        <input
                                                            className="w-full rounded-lg border border-gray-300 p-2 text-sm"
                                                            placeholder="New NO question text"
                                                            value={noChildForm.data.question_text}
                                                            onChange={(e) => noChildForm.setData('question_text', e.target.value)}
                                                        />
                                                        <button
                                                            type="submit"
                                                            disabled={noChildForm.processing || !noChildForm.data.question_text}
                                                            className="w-full rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50"
                                                        >
                                                            Create NO child
                                                        </button>
                                                    </>
                                                )}
                                            </form>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    ) : (
                        <div className="mt-4 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-6 text-sm text-gray-600">
                            No questions found for this Problem yet.
                            <form className="mt-4 max-w-xl" onSubmit={submitRoot}>
                                <label className="text-xs font-medium text-gray-700">Create root question</label>
                                <input
                                    className="mt-1 w-full rounded-lg border border-gray-300 p-2 text-sm"
                                    placeholder="First question for this problem"
                                    value={rootForm.data.question_text}
                                    onChange={(e) => rootForm.setData('question_text', e.target.value)}
                                />
                                <button
                                    type="submit"
                                    disabled={rootForm.processing || !rootForm.data.question_text}
                                    className="mt-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    Create root
                                </button>
                            </form>
                        </div>
                    )}
            </div>

            <div className="mt-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div className="text-sm font-semibold text-gray-800">Clone / Reuse a Sub-Flow</div>
                <div className="mt-2 text-xs text-gray-600">Search by question id or text to quickly find attach points.</div>
                <div className="mt-3">
                    <input
                        className="w-full rounded-lg border border-gray-300 p-2 text-sm"
                        placeholder="Search in source/target questions (id or text)…"
                        value={cloneSearch}
                        onChange={(e) => setCloneSearch(e.target.value)}
                    />
                </div>
                <div className="mt-2">
                    <button
                        type="button"
                        onClick={useSelectedAsAttachTarget}
                        disabled={!selectedNodeId || cloneForm.data.attach_mode === 'root'}
                        className="rounded-lg border border-blue-300 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100 disabled:opacity-50"
                    >
                        Use Selected Tree Node as Attach Target
                    </button>
                </div>

                <form className="mt-4 grid gap-4 md:grid-cols-3" onSubmit={submitClone}>
                    <div>
                        <label className="text-xs font-medium text-gray-700">Source Problem</label>
                        <select
                            className="mt-1 w-full rounded-lg border border-gray-300 p-2"
                            value={sourceProblemId}
                            onChange={(e) => setSourceProblemId(e.target.value)}
                        >
                            <option value="">Choose source problem</option>
                            {problems.map((p) => (
                                <option key={p.id} value={p.id}>
                                    {p.name}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label className="text-xs font-medium text-gray-700">Source Start Question</label>
                        <select
                            className="mt-1 w-full rounded-lg border border-gray-300 p-2"
                            value={cloneForm.data.source_question_id}
                            onChange={(e) => cloneForm.setData('source_question_id', e.target.value)}
                            disabled={!sourceProblemId}
                        >
                            <option value="">Choose question</option>
                            {filteredSourceQuestions.map((q) => (
                                <option key={q.id} value={q.id}>
                                    #{q.id} — {String(q.text).slice(0, 90)}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label className="text-xs font-medium text-gray-700">Attach Mode</label>
                        <select
                            className="mt-1 w-full rounded-lg border border-gray-300 p-2"
                            value={cloneForm.data.attach_mode}
                            onChange={(e) => cloneForm.setData('attach_mode', e.target.value)}
                            disabled={!selectedProblemId}
                        >
                            <option value="yes">Attach to YES</option>
                            <option value="no">Attach to NO</option>
                            <option value="root">Set as ROOT (only if empty)</option>
                        </select>
                    </div>

                    {cloneForm.data.attach_mode !== 'root' ? (
                        <div className="md:col-span-2">
                            <label className="text-xs font-medium text-gray-700">Attach To (Target Question)</label>
                            <select
                                className="mt-1 w-full rounded-lg border border-gray-300 p-2"
                                value={cloneForm.data.target_attach_question_id}
                                onChange={(e) => cloneForm.setData('target_attach_question_id', e.target.value)}
                                disabled={!selectedProblemId}
                            >
                                <option value="">Choose target question</option>
                                {filteredTargetQuestions.map((q) => (
                                    <option key={q.id} value={q.id}>
                                        #{q.id} — {String(q.text).slice(0, 90)}
                                    </option>
                                ))}
                            </select>
                            <div className="mt-1 text-xs text-gray-500">
                                Choose where the cloned sub-flow should continue. If empty, selected tree node is used.
                            </div>
                        </div>
                    ) : (
                        <div className="md:col-span-2 rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs text-gray-600">
                            Root cloning is allowed only when the Target Problem has no questions yet.
                        </div>
                    )}

                    <div className="md:col-span-3 flex items-center justify-end gap-3">
                        {cloneForm.processing ? <div className="text-xs text-gray-500">Cloning…</div> : null}
                        <button
                            type="submit"
                            disabled={!canClone || cloneForm.processing}
                            className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700 disabled:opacity-50"
                        >
                            Clone & Attach
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
