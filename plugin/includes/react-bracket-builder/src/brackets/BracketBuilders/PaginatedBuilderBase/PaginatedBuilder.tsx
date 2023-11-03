import { useState, useEffect } from 'react'
import { PaginatedBuilderProps } from './types'

export const PaginatedBuilder = (props: PaginatedBuilderProps) => {
  const {
    matchTree,
    setMatchTree,
    darkMode,
    setDarkMode,
    handleSubmit,
    processing,
    StartPageComponent,
    BracketPagesComponent,
    EndPageComponent,
  } = props

  const [page, setPage] = useState(StartPageComponent ? 'start' : 'bracket')

  useEffect(() => {
    if (!matchTree) {
      return
    }
    if (matchTree.allPicked()) {
      setPage('end')
    } else if (matchTree.anyPicked()) {
      setPage('bracket')
    }
  }, [])

  const onStart = () => {
    setPage('bracket')
  }

  const onFinished = () => {
    setPage('end')
  }

  let element: JSX.Element | null = null

  switch (page) {
    case 'start':
      element = <StartPageComponent matchTree={matchTree} onStart={onStart} />
      break
    case 'bracket':
      element = (
        <BracketPagesComponent
          matchTree={matchTree}
          setMatchTree={setMatchTree}
          onFinished={onFinished}
        />
      )
      break
    case 'end':
      element = (
        <EndPageComponent
          matchTree={matchTree}
          darkMode={darkMode}
          setDarkMode={setDarkMode}
          onEditClick={onStart}
          handleSubmit={handleSubmit}
          processing={processing}
        />
      )
      break
  }

  return element
}
