import React from 'react'
import { BracketMetaContext, BracketMeta } from '../../context'

export const WithBracketMeta = (Component: React.ComponentType<any>) => {
  return (props: any) => {
    const [bracketMeta, setBracketMeta] = React.useState<BracketMeta>({})
    return (
      <BracketMetaContext.Provider value={bracketMeta}>
        <Component
          bracketMeta={bracketMeta}
          setBracketMeta={setBracketMeta}
          {...props}
        />
      </BracketMetaContext.Provider>
    )
  }
}
