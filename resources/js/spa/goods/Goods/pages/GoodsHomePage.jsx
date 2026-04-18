import React from 'react';
import { Card } from '../../GoodsModuleShared';

export function GoodsHomePage({ Layout }) {
  return <Layout><div className="mx-auto grid max-w-5xl gap-4 p-6 md:grid-cols-3"><Card title="Goods Index" text="React + Tailwind replacement for backend/goods/index.blade.php" /><Card title="Goods Create" text="React + Tailwind replacement for backend/goods/create.blade.php" /><Card title="Goods Edit" text="React + Tailwind replacement for backend/goods/edit.blade.php" /></div></Layout>;
}

