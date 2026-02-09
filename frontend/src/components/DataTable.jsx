import React from 'react';

const DataTable = ({ columns, data, emptyMessage = "Tidak ada data tersedia", renderActions }) => {
  return (
    <div className="table-wrapper">
      <table className="custom-table">
        <thead>
          <tr>
            {columns.map((col, index) => (
              <th key={index} style={col.style || {}}>
                {col.header}
              </th>
            ))}
            {renderActions && <th style={{ textAlign: 'center' }}>AKSI</th>}
          </tr>
        </thead>
        <tbody>
          {data.length > 0 ? (
            data.map((item, rowIndex) => (
              <tr key={rowIndex}>
                {columns.map((col, colIndex) => (
                  <td key={colIndex} style={col.style || {}}>
                    {col.key.split('.').reduce((obj, key) => obj?.[key], item)}
                  </td>
                ))}
                {renderActions && (
                  <td className="actions" style={{ textAlign: 'center' }}>
                    {renderActions(item)}
                  </td>
                )}
              </tr>
            ))
          ) : (
            <tr>
              <td 
                colSpan={columns.length + (renderActions ? 1 : 0)} 
                style={{ textAlign: 'center', padding: '30px', color: '#94a3b8' }}
              >
                {emptyMessage}
              </td>
            </tr>
          )}
        </tbody>
      </table>
    </div>
  );
};

export default DataTable;