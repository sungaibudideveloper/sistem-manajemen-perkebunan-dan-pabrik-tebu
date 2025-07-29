import React from 'react';

type Props = {
  user: {
    usernm: string;
  } | null;
};

export default function TestSimple({ user }: Props) {
  return (
    <div className="p-8">
      <h1 className="text-2xl font-bold text-blue-600">
        React + Inertia Test (TypeScript)
      </h1>
      <p className="mt-4 text-gray-600">
        Hello {user?.usernm || 'Guest'}!
      </p>
      <div className="mt-6 p-4 bg-green-100 rounded-lg">
        <p className="text-green-800">✅ TypeScript + React working!</p>
      </div>
    </div>
  );
}
