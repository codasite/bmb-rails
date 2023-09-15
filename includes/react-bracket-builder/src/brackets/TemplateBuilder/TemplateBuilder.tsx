import React, { useState } from 'react'
import './template-builder.scss'
import { MatchTree } from '../shared/models/MatchTree'
//@ts-ignore
import { AddTeamsPage } from './AddTeamsPage'
import { NumTeamsPage } from './NumTeamsPage'

const defaultBracketName = "MY BRACKET NAME"

interface TemplateBuilderProps {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  saveTemplateLink?: string
  saveTournamentLink?: string
}

const TemplateBuilder = (props: TemplateBuilderProps) => {
  const {
    matchTree,
    setMatchTree,
    saveTemplateLink,
    saveTournamentLink
  } = props

  const [currentPage, setCurrentPage] = useState('add-teams')
  const [bracketTitle, setBracketTitle] = useState(defaultBracketName)

  const onAddTeamsClick = () => {
    setCurrentPage('add-teams')
  }

  return (
    <div className='wpbb-reset tw-uppercase'>
      {currentPage === 'num-teams' &&
        <NumTeamsPage
          matchTree={matchTree}
          setMatchTree={setMatchTree}
          onAddTeamsClick={onAddTeamsClick}
          bracketTitle={bracketTitle}
          setBracketTitle={setBracketTitle}
        />
      }
      {currentPage === 'add-teams' &&
        <AddTeamsPage
          matchTree={matchTree}
          setMatchTree={setMatchTree}
          bracketTitle={bracketTitle}
          handleBack={() => setCurrentPage('num-teams')}
          handleSaveTemplate={() => { }}
          handleSaveTournament={() => { }}
        />
      }
    </div>
  )
}

export default TemplateBuilder