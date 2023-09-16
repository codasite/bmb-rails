import React, { useState } from 'react'
import { bracketApi } from '../shared/api/bracketApi'
import { MatchTree } from '../shared/models/MatchTree'
//@ts-ignore
import { AddTeamsPage } from './AddTeamsPage'
import { NumTeamsPage } from './NumTeamsPage'
import { TemplateReq } from '../shared/api/types/bracket'

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

  const [currentPage, setCurrentPage] = useState('num-teams')
  const [bracketTitle, setBracketTitle] = useState(defaultBracketName)

  const handleAddTeamsClick = () => {
    setCurrentPage('add-teams')
  }

  const handleSaveTemplateClick = () => {
    console.log('save template')
    if (!matchTree) {
      return
    }
    const req: TemplateReq = {
      title: bracketTitle,
      numTeams: matchTree.getNumTeams(),
      wildcardPlacement: matchTree.getWildcardPlacement(),
      matches: matchTree.toMatchReq()
    }
    console.log(req)

    // bracketApi.createTemplate()


  }

  const handleSaveTournamentClick = () => {
    console.log('save tournament')
  }

  return (
    <div className='wpbb-reset tw-uppercase'>
      {currentPage === 'num-teams' &&
        <NumTeamsPage
          matchTree={matchTree}
          setMatchTree={setMatchTree}
          onAddTeamsClick={handleAddTeamsClick}
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
          handleSaveTemplate={handleSaveTemplateClick}
          handleSaveTournament={handleSaveTournamentClick}
        />
      }
    </div>
  )
}

export default TemplateBuilder