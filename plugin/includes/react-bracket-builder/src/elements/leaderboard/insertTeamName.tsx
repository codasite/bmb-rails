import React from 'react'
import { render } from 'react-dom'
import { ScaledSpan } from '../../brackets/shared/components/TeamSlot/ScaledSpan'
export const insertLeaderboardTeamName = (wpbbAppObj: any) => {
  //get all elements with class name "wpbb-lb-winning-team-name-container"
  console.log('running')
  const divs = document.getElementsByClassName(
    'wpbb-lb-winning-team-name-container'
  )
  for (const d of divs) {
    const teamName = d.getAttribute('data-team-name')
    console.log('d', d)
    console.log('teamName', teamName)
    render(<ScaledSpan targetWidth={50}>{teamName}</ScaledSpan>, d)
  }
}
