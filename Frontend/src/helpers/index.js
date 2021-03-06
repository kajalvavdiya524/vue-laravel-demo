export const only = (obj, ...keys) => Object.fromEntries(Object.entries(obj).filter(([k]) => keys.includes(k)));

export const pick = (obj, path) => {
  const pa = path.split('.');
  if (pa.length === 1) return obj[path];
  const p = pa.shift();
  return pick(obj[p], pa.join('.'));
};
