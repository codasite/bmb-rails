// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import { render, screen, fireEvent } from '@testing-library/react'
import { BaseTeamSlot } from './BaseTeamSlot' // Import your component
import '@testing-library/jest-dom/jest-globals' // for additional matchers like toBeInTheDocument
import { MatchTree } from '../../models/MatchTree'
import { MatchNode } from '../../models/operations/MatchNode'
import { Team } from '../../models/Team'
import { userEvent } from '@testing-library/user-event'

// Mock some functions and props
const mockOnTeamClick = jest.fn()
const team = new Team('Team A')
const match = new MatchNode({ matchIndex: 2, roundIndex: 1, depth: 1 })
const matchTree = MatchTree.fromNumTeams(8)

describe('BaseTeamSlot', () => {
  afterEach(() => {
    jest.clearAllMocks()
  })

  test('renders with correct text and styles', () => {
    render(
      <BaseTeamSlot
        team={team}
        match={match}
        teamPosition="left"
        height={50}
        width={115}
        onTeamClick={mockOnTeamClick}
        matchTree={matchTree}
        placeholder="Placeholder"
      />
    )

    // Check if the text is rendered
    expect(screen.getByText('Team A')).toBeInTheDocument()

    // Check if the element has the correct data-testid
    const teamSlot = screen.getByTestId('team-slot-round-1-match-2-left')
    expect(teamSlot).toBeInTheDocument()

    // Verify the base styles are applied
    expect(teamSlot).toMatchSnapshot()
  })

  test('calls onTeamClick when clicked', () => {
    render(
      <BaseTeamSlot
        team={team}
        match={match}
        teamPosition="left"
        height={50}
        width={115}
        onTeamClick={mockOnTeamClick}
        matchTree={matchTree}
      />
    )

    const teamSlot = screen.getByTestId('team-slot-round-1-match-2-left')

    // Simulate a click event
    fireEvent.click(teamSlot)

    // Ensure the click handler was called
    expect(mockOnTeamClick).toHaveBeenCalledTimes(1)
    expect(mockOnTeamClick).toHaveBeenCalledWith(match, 'left', team)
  })

  test('does not call onTeamClick when teamClickDisabled', () => {
    const teamClickDisabled = jest.fn(() => true)

    render(
      <BaseTeamSlot
        team={team}
        match={match}
        teamPosition="left"
        height={50}
        width={115}
        onTeamClick={mockOnTeamClick}
        matchTree={matchTree}
        teamClickDisabled={teamClickDisabled}
      />
    )

    const teamSlot = screen.getByTestId('team-slot-round-1-match-2-left')

    // Simulate a click event
    fireEvent.click(teamSlot)

    // Ensure the click handler was not called
    expect(mockOnTeamClick).not.toHaveBeenCalled()
  })

  test('renders placeholder if no team name is provided', () => {
    render(
      <BaseTeamSlot
        match={match}
        teamPosition="left"
        height={50}
        width={115}
        placeholder="No Team"
        matchTree={matchTree}
      />
    )

    // Check if the placeholder is rendered
    expect(screen.getByText('No Team')).toBeInTheDocument()
  })
})
