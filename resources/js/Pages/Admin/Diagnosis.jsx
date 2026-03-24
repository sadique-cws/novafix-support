import React, { useEffect, useMemo, useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';

function TreeNode({ nodeId, nodes, depth = 0, expanded, toggle, selectedId, onSelect }) {
    const node = nodes?.[nodeId];
    if (!node) return null;

    const hasChildren = Boolean(node.yes || node.no);
    const isExpanded = expanded[nodeId] ?? depth < 2;
    const isSelected = String(selectedId) === String(nodeId);

    return (
        <div className="mt-2">
            <div
                className={`flex items-start gap-2 rounded-lg border bg-white p-3 shadow-sm cursor-pointer ${
                    isSelected ? 'border-indigo-400 ring-2 ring-indigo-100' : 'border-gray-200 hover:border-gray-300'
                }`}
                style={{ marginLeft: depth * 16 }}
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
                        className="mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded border border-gray-300 text-gray-700 hover:bg-gray-50"
                        aria-label="Toggle node"
                    >
                        {isExpanded ? '−' : '+'}
                    </button>
                ) : (
                    <span className="mt-1 inline-block h-6 w-6" />
                )}
                <div className="min-w-0 flex-1">
                    <div className="text-xs text-gray-500">#{node.id}</div>
                    <div className="mt-0.5 text-sm font-semibold leading-snug text-gray-900 break-words">{node.text}</div>
                    <div className="mt-2 flex flex-wrap gap-2 text-xs">
                        <span className="rounded-md bg-green-50 px-2 py-1 text-green-700">YES → {node.yes ?? 'END'}</span>
                        <span className="rounded-md bg-red-50 px-2 py-1 text-red-700">NO → {node.no ?? 'END'}</span>
                    </div>
                </div>
            </div>

            {hasChildren && isExpanded ? (
                <div className="mt-2">
                    {node.yes ? (
                        <div>
                            <div className="text-xs font-semibold text-green-700" style={{ marginLeft: (depth + 1) * 16 }}>
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
                            />
                        </div>
                    ) : null}
                    {node.no ? (
                        <div className="mt-2">
                            <div className="text-xs font-semibold text-red-700" style={{ marginLeft: (depth + 1) * 16 }}>
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
                            />
                        </div>
                    ) : null}
                </div>
            ) : null}
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

    const [sourceProblemId, setSourceProblemId] = useState('');
    const [sourceQuestions, setSourceQuestions] = useState([]);
    const [targetQuestions, setTargetQuestions] = useState([]);

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

    useEffect(() => {
        setSelectedBrandId('');
        setSelectedModelId('');
        setSelectedProblemId('');
        setTree(null);
        setTargetQuestions([]);
        setExpanded({});
        setSelectedNodeId('');
    }, [selectedDeviceId]);

    useEffect(() => {
        setSelectedModelId('');
        setSelectedProblemId('');
        setTree(null);
        setTargetQuestions([]);
        setExpanded({});
        setSelectedNodeId('');
    }, [selectedBrandId]);

    useEffect(() => {
        setSelectedProblemId('');
        setTree(null);
        setTargetQuestions([]);
        setExpanded({});
        setSelectedNodeId('');
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
        (cloneForm.data.attach_mode === 'root' || cloneForm.data.target_attach_question_id);

    const submitClone = (e) => {
        e.preventDefault();
        cloneForm.post('/admin/diagnosis/clone', {
            preserveScroll: true,
            onSuccess: () => {
                refreshTarget();
            },
        });
    };

    const selectedNode = tree?.nodes?.[selectedNodeId] ?? null;

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

    return (
        <div>
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

            <div className="mt-6 grid gap-4 md:grid-cols-4">
                <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="text-sm font-semibold text-gray-800">Target Flow</div>
                    <div className="mt-3 space-y-3">
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

                <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm md:col-span-3">
                    <div className="flex items-center justify-between">
                        <div className="text-sm font-semibold text-gray-800">Tree Viewer</div>
                        {tree?.count ? <div className="text-xs text-gray-500">{tree.count} questions</div> : null}
                    </div>

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
                            <div className="max-h-[70vh] overflow-auto pr-2">
                                {tree.roots.map((rootId) => (
                                    <TreeNode
                                        key={rootId}
                                        nodeId={rootId}
                                        nodes={tree.nodes}
                                        expanded={expanded}
                                        toggle={toggle}
                                        selectedId={selectedNodeId}
                                        onSelect={(id) => setSelectedNodeId(String(id))}
                                    />
                                ))}
                            </div>
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
            </div>

            <div className="mt-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div className="text-sm font-semibold text-gray-800">Clone / Reuse a Sub-Flow</div>
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
                            {sourceQuestions.map((q) => (
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
                                {targetQuestions.map((q) => (
                                    <option key={q.id} value={q.id}>
                                        #{q.id} — {String(q.text).slice(0, 90)}
                                    </option>
                                ))}
                            </select>
                            <div className="mt-1 text-xs text-gray-500">Choose where the cloned sub-flow should continue.</div>
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
