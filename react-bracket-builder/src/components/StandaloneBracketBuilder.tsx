// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React, { useContext, useEffect, useState } from 'react'
import { railsBracketApi } from '../brackets/shared/api/railsBracketApi'
import { MatchTree } from '../brackets/shared/models/MatchTree'
import { AddTeamsPage } from '../brackets/BracketBuilders/BracketBuilder/AddTeamsPage'
import { NumTeamsPage, NumTeamsPickerState } from '../brackets/BracketBuilders/BracketBuilder/NumTeamsPage'
import { BracketReq, BracketRes } from '../brackets/shared/api/types/bracket'
import {
  WithBracketMeta,
  WithMatchTree,
} from '../brackets/shared/components/HigherOrder'
import { BracketMetaContext } from '../brackets/shared/context/context'
import { WildcardPlacement } from '../brackets/shared/models/WildcardPlacement'

const defaultInitialPickerIndex = 0
export const teamPickerDefaults = [16, 32, 64]
export const teamPickerMin = [2, 17, 33]
export const teamPickerMax = [31, 63, 64]

interface StandaloneBracketBuilderProps {
  bracket?: BracketRes
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
}

const StandaloneBracketBuilder = (props: StandaloneBracketBuilderProps) => {
  const { matchTree, setMatchTree, bracket } = props
  const [currentPage, setCurrentPage] = useState('num-teams')
  const [numTeams, setNumTeams] = useState(
    teamPickerDefaults[defaultInitialPickerIndex]
  )
  const [wildcardPlacement, setWildcardPlacement] = useState(
    WildcardPlacement.Split
  )
  const [teamPickerState, setTeamPickerState] = useState<NumTeamsPickerState[]>(
    teamPickerDefaults.map((val, i) => ({
      currentValue: val,
      selected: i === defaultInitialPickerIndex,
    }))
  )
  const [month, setMonth] = useState('')
  const [year, setYear] = useState('')
  const [processing, setProcessing] = useState(false)
  const [showBackButton, setShowBackButton] = useState(true)
  const [savedBrackets, setSavedBrackets] = useState<BracketRes[]>([])
  const { bracketMeta, setBracketMeta } = useContext(BracketMetaContext)

  useEffect(() => {
    setBracketMeta?.({
      ...bracketMeta,
      date: `${month} ${year}`,
    })
  }, [month, year])

  useEffect(() => {
    setBracketMeta?.({
      title: bracketMeta?.title || '',
      date: bracketMeta?.date || '',
    })
    
    // Load existing brackets
    loadBrackets()
  }, [])

  const loadBrackets = async () => {
    try {
      const brackets = await railsBracketApi.getBrackets()
      setSavedBrackets(brackets)
    } catch (error) {
      console.error('Error loading brackets:', error)
    }
  }

  const handleAddTeamsClick = () => {
    setCurrentPage('add-teams')
  }

  const getBracketReq = () => {
    const req: BracketReq = {
      title: bracketMeta.title,
      month: month,
      year: year,
      numTeams: numTeams,
      wildcardPlacement: wildcardPlacement,
      matches: matchTree?.toMatchReq() || [],
      status: 'private',
      roundNames: bracketMeta.roundNames,
    }
    return req
  }

  const handleSaveBracketClick = async () => {
    if (!matchTree || !matchTree.allTeamsAdded()) {
      alert('Please add all teams before saving the bracket.')
      return
    }
    
    setProcessing(true)
    try {
      const bracketReq = getBracketReq()
      const savedBracket = await railsBracketApi.createBracket(bracketReq)
      console.log('Bracket saved successfully:', savedBracket)
      alert(`Bracket "${bracketReq.title}" saved successfully!`)
      
      // Refresh the brackets list
      await loadBrackets()
      
      // Reset to create a new bracket
      setCurrentPage('num-teams')
      setMatchTree?.(null)
      setBracketMeta?.({ title: '', date: '', roundNames: [] })
    } catch (error) {
      console.error('Error saving bracket:', error)
      alert('Error saving bracket. Please make sure the Rails server is running on http://localhost:3000')
    } finally {
      setProcessing(false)
    }
  }

  return (
    <div style={{ padding: '20px', maxWidth: '1200px', margin: '0 auto' }}>
      <div style={{ marginBottom: '30px' }}>
        <h1 style={{ fontSize: '2.5rem', fontWeight: 'bold', marginBottom: '10px', color: '#1f2937' }}>
          🏆 Bracket Builder
        </h1>
        <p style={{ fontSize: '1.1rem', color: '#6b7280', marginBottom: '20px' }}>
          Create professional tournament brackets without WordPress
        </p>
        
        {savedBrackets.length > 0 && (
          <div style={{ 
            backgroundColor: '#f3f4f6', 
            padding: '15px', 
            borderRadius: '8px', 
            marginBottom: '20px' 
          }}>
            <h3 style={{ fontSize: '1.2rem', fontWeight: 'bold', marginBottom: '10px' }}>
              Saved Brackets ({savedBrackets.length})
            </h3>
            <div style={{ display: 'flex', flexWrap: 'wrap', gap: '10px' }}>
              {savedBrackets.map((bracket) => (
                <div key={bracket.id} style={{
                  backgroundColor: 'white',
                  padding: '10px',
                  borderRadius: '6px',
                  border: '1px solid #d1d5db',
                  minWidth: '200px'
                }}>
                  <div style={{ fontWeight: 'bold' }}>{bracket.title}</div>
                  <div style={{ fontSize: '0.9rem', color: '#6b7280' }}>
                    {bracket.numTeams} teams • {bracket.month} {bracket.year}
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
      
      <div className="wpbb-reset tw-uppercase">
        {currentPage === 'num-teams' && (
          <NumTeamsPage
            matchTree={matchTree}
            setMatchTree={setMatchTree}
            onAddTeamsClick={handleAddTeamsClick}
            numTeams={numTeams}
            setNumTeams={setNumTeams}
            teamPickerDefaults={teamPickerDefaults}
            teamPickerMin={teamPickerMin}
            teamPickerMax={teamPickerMax}
            wildcardPlacement={wildcardPlacement}
            setWildcardPlacement={setWildcardPlacement}
            teamPickerState={teamPickerState}
            setTeamPickerState={setTeamPickerState}
          />
        )}
        {currentPage === 'add-teams' && (
          <AddTeamsPage
            matchTree={matchTree}
            setMatchTree={setMatchTree}
            handleBack={() => setCurrentPage('num-teams')}
            handleSaveBracket={handleSaveBracketClick}
            month={month}
            setMonth={setMonth}
            year={year}
            setYear={setYear}
            processing={processing}
            showBackButton={showBackButton}
          />
        )}
      </div>
    </div>
  )
}

const WrappedStandaloneBracketBuilder = WithMatchTree(WithBracketMeta(StandaloneBracketBuilder))
export default WrappedStandaloneBracketBuilder
