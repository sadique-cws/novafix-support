# NovaFix Support - Change Log and Replication Guide

This document records all major implementation changes completed in this project and provides a clear playbook to repeat the same work in another project.

## 1) Scope Summary

Implemented end-to-end improvements for the diagnosis flow builder and tree explorer:

- Added backend subtree clone service for question flows (YES/NO branch aware).
- Added admin diagnosis API endpoints for tree fetch, question list, clone attach, root creation, branch creation, and question update.
- Built a React (Inertia) admin diagnosis page for full tree operations.
- Added advanced D3 tree diagram with pan/zoom and branch navigation.
- Improved layout consistency (desktop sidebar + mobile drawer).
- Fixed React preamble issue by adding Vite React refresh directives in Blade layouts.

## 2) Backend Changes

### 2.1 Service: Sub-flow cloning

File:
- app/Services/QuestionFlowCloner.php

What was implemented:
- Recursive subtree clone from a selected source question.
- Supports attach modes:
  - yes: attach cloned root to target question YES branch
  - no: attach cloned root to target question NO branch
  - root: set as root only when target problem has no existing questions
- Safety checks:
  - Source question must belong to selected source problem.
  - Target attach question must belong to target problem.
  - Prevent overwriting existing YES/NO branches.
  - Detect cycles in source tree.
- Data handling:
  - Copies question text/description/image_url.
  - Sets image_file_id to null in cloned nodes to avoid accidental shared media delete behavior.

### 2.2 Controller: Admin diagnosis endpoints

File:
- app/Http/Controllers/AdminDiagnosisController.php

Implemented endpoints/methods:
- index(): React page payload (devices/brands/models/problems)
- tree(Problem $problem): returns graph JSON (roots, nodes, count)
- questions(Problem $problem): question id/text list for dropdown/search
- clone(Request,...): uses QuestionFlowCloner
- updateQuestion(Request, Question): edit selected question text
- createRootQuestion(Request): first/root question creation
- createBranchQuestion(Request): create YES or NO child and attach

### 2.3 Routes

File:
- routes/web.php

Added/used admin routes:
- GET /admin/diagnosis/tree/{problem}
- GET /admin/diagnosis/questions/{problem}
- POST /admin/diagnosis/clone
- POST /admin/diagnosis/root
- POST /admin/diagnosis/branch
- PUT /admin/diagnosis/question/{question}

## 3) Frontend - React Admin Diagnosis Page

Primary file:
- resources/js/Pages/Admin/Diagnosis.jsx

### 3.1 Core diagnosis workspace

Implemented:
- Cascading selectors: Device -> Brand -> Model -> Problem
- Tree loading from API on problem selection
- Selected node editing panel
- Root creation when problem has no tree
- YES/NO child creation from selected node
- Search for nodes by id/text
- Focus selected subtree view
- Expand/collapse all in card mode

### 3.2 Clone/reuse sub-flow UX

Implemented:
- Source problem + source start question selection
- Attach mode selection (YES/NO/ROOT)
- Target question selection
- Search in source/target question lists
- "Use Selected Tree Node as Attach Target" shortcut
- Auto fallback: if attach mode is YES/NO and no target chosen, selected node is used

### 3.3 D3 diagram mode

Implemented D3 tree component:
- D3 hierarchy + tree layout
- YES/NO colored links (green/red)
- Node click -> selects node
- Node double-click -> center + zoom to node
- Branch action chips near node:
  - click YES chip -> navigate to YES child
  - click NO chip -> navigate to NO child
- Search hit highlight on nodes
- Canvas controls:
  - Zoom +
  - Zoom -
  - Zoom Node (selected)
  - Reset
- Pan/zoom interaction:
  - mouse drag pan
  - wheel zoom
  - zoom bounds [0.25, 2.5]
- View toggle:
  - D3 Tree mode
  - Card Tree mode (fallback)

### 3.4 Package update

File:
- package.json

Added dependency:
- d3 ^7.9.0

## 4) Layout and Navigation Improvements

### 4.1 Unified app shell behavior

Files:
- resources/views/components/app-shell.blade.php
- resources/views/components/nav-links.blade.php

Implemented:
- Desktop sidebar for larger screens
- Mobile drawer navigation
- role display in sidebar for signed-in user
- consistent nav links for admin/staff

### 4.2 React preamble fix

Files:
- resources/views/inertia/app.blade.php
- resources/views/components/layouts/app.blade.php

Fix:
- Added @viteReactRefresh before @vite(...) to resolve:
  - "@vitejs/plugin-react can't detect preamble"

## 5) Validation Performed

Commands used:
- php -l app/Services/QuestionFlowCloner.php
- php -l app/Http/Controllers/AdminDiagnosisController.php (implicitly validated by build/runtime route usage)
- npm run build
- php artisan view:clear (after Blade refresh fix)

Build result:
- Frontend build completed successfully after changes.

## 6) Replication Playbook (Do Same in Another Project)

Use this exact order in your next project:

1. Create backend clone service
- Add QuestionFlowCloner service with recursion + cycle detection.
- Implement attach modes yes/no/root.

2. Expose admin diagnosis APIs
- tree endpoint (roots + nodes graph JSON)
- questions endpoint for dropdown/search
- clone endpoint using service
- root create, branch create, question update endpoints

3. Build React admin diagnosis page (Inertia)
- device/brand/model/problem cascading selects
- tree load + selected node panel
- clone panel with attach modes

4. Add D3 tree mode
- implement D3 hierarchy/tree renderer
- add pan/zoom behavior and controls
- add node click/double-click actions
- add branch chips YES/NO for child navigation

5. Keep card tree fallback
- maintain a simple React card/tree renderer for debugging and mobile fallback

6. Fix Vite React preamble in Blade
- ensure @viteReactRefresh exists in all Blade entry layouts loading React bundle

7. Final QA checklist
- select problem -> tree loads
- select node -> right panel updates
- YES/NO child creation works
- clone attach works in yes/no/root modes
- branch navigation in D3 works
- zoom/pan/reset/zoom-node controls work
- npm run build passes

## 7) Recommended Template Strategy for Faster Reuse

Best idea for future projects:

- Extract diagnosis builder into a reusable module:
  - Backend package folder:
    - QuestionFlowCloner service
    - AdminDiagnosisController
    - route file
  - Frontend module:
    - Diagnosis.jsx
    - D3TreeGraph component
- Keep API contract stable (tree/questions/clone/root/branch/update).
- In new project, only map model/table names and route prefix.

This reduces future setup from days to hours.

## 8) Files to Copy First in New Project

Start with these high-value files:

- app/Services/QuestionFlowCloner.php
- app/Http/Controllers/AdminDiagnosisController.php
- resources/js/Pages/Admin/Diagnosis.jsx
- routes/web.php (diagnosis route group)
- resources/views/inertia/app.blade.php (@viteReactRefresh check)
- resources/views/components/layouts/app.blade.php (@viteReactRefresh check)
- resources/views/components/app-shell.blade.php
- resources/views/components/nav-links.blade.php

Then adapt model imports, auth middleware, and route names.

---

Prepared for migration and reuse across another project.
