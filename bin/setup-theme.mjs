#!/usr/bin/env node

import inquirer from 'inquirer'
import fs from 'fs-extra'
import chalk from 'chalk'
import path from 'path'

const setupFlagPath = path.join(process.cwd(), '.setup_done')

// Create a file
const createFile = (filePath, content) => {
    if (fs.existsSync(filePath)) {
        console.log(chalk.red(`❌ The ${filePath} file already exist.`))
        return
    }

    try {
        const filename = path.basename(filePath)
        fs.writeFileSync(filePath, content)
        console.log(chalk.green(`✔ Create: ${filename}`))
    } catch (error) {
        console.log(chalk.red(`❌ Error creating file: ${error.message}`))
    }
}

// Replace content in a file
const replaceInFile = (filePath, replacements) => {
    if (!fs.existsSync(filePath)) {
        console.log(chalk.red(`❌ The file ${filePath} does not exist.`))
        return
    }

    let content = fs.readFileSync(filePath, 'utf8')
    for (const [search, replace] of Object.entries(replacements)) {
        const regex = new RegExp(search, 'g')
        content = content.replace(regex, replace)
    }

    fs.writeFileSync(filePath, content, 'utf8')
    console.log(chalk.green(`✔ Update : ${filePath}`))
}

const stringToSlug = (string) => {
    return string
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-zA-Z0-9]/g, '-')
        .toLowerCase()
        .replace(/-+/g, '-')
        .replace(/^-+|-+$/g, '')
}

// Filters
const filtersTextDomain = (input, answers) => {
    return input || answers.themeName.replace(/\s+/g, '').slice(0, 3).toLowerCase()
}

const filterDescription = (input, answers) => {
    return input || `Template custom for ${answers.themeName}.`
}

// Validate
const validateUri = (input) => {
    if (!input) return true

    const regex = /^(http|https):\/\/[^ "]+$/
    return regex.test(input) ? true : 'Please enter a valid URI.'
}

const validateComposerName = (answers) => {
    const vendor = answers.authorName.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')
    const packageName = answers.themeName.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')
    return `${vendor}/${packageName}`
}

// Questions
const questions = [
    { name: 'themeName', message: 'Theme name:', validate: input => input ? true : 'Theme name is required.' },
    { name: 'authorName', message: 'Author name:', validate: input => input ? true : 'Author name is required.' },
    { name: 'authorUri', message: 'Author URI:', validate: validateUri },
    { name: 'description', message: 'Short description:', filter: filterDescription },
    { name: 'textDomain', message: 'Text domain for translate:', filter: filtersTextDomain },
]

// Check if already configured
const confirmIfAlreadyRun = async () => {
    if (fs.existsSync(setupFlagPath)) {
        const { proceed } = await inquirer.prompt([
            {
                type: 'confirm',
                name: 'proceed',
                message: chalk.yellow('This project has already been configured. Do you want to run the setup again?'),
                default: false,
            }
        ])

        if (!proceed) {
            console.log(chalk.blue('\n Setup cancelled.'))
            process.exit(0)
        }
    }
}

// Start the script
const run = async () => {
    await confirmIfAlreadyRun()

    const answers = await inquirer.prompt(questions)

    console.log(chalk.blue('\n🔄 Updating files...\n'))

    replaceInFile('style.css', {
        'Theme Name: .*': `Theme Name: ${answers.themeName}`,
        'Text Domain: .*': `Text Domain: ${answers.textDomain}`,
        'Author: .*': `Author: ${answers.authorName}`,
        'Author URI: .*': `Author URI: ${answers.authorUri}`,
        'Description: .*': `Description: ${answers.description}`
    })

    replaceInFile('package.json', {
        '"name": .*': `"name": "${stringToSlug(answers.themeName)}",`,
        '"description": .*': `"description": "${answers.description}",`,
        '"author": .*': `"author": "${answers.authorName}",`,
    })

    replaceInFile('composer.json', {
        '"name": ".*"': `"name": "${validateComposerName(answers)}"`,
        '"authors":\\s*\\[\\s*{\\s*"name":\\s*".*?"\\s*}': `"authors": [ \n \t\t{ "name": "${answers.authorName}" }`
    })

    replaceInFile('functions.php', {
        "define\\('TRANSLATE_DOMAIN', '.*'\\);": `define('TRANSLATE_DOMAIN', '${answers.textDomain}');`
    })

	replaceInFile('views/components/backToTop.twig', {
        'tpl': `${answers.textDomain}`
    })

    replaceInFile('views/pages/404.twig', {
        'tpl': `${answers.textDomain}`
    })

    replaceInFile('views/layout/base.twig', {
        'tpl': `${answers.textDomain}`
    })

    // Create .env file
    const envPath = path.join(process.cwd(), `.env`)
    createFile(envPath, '')

    // Create the flag file to indicate setup was run
    fs.writeFileSync(setupFlagPath, 'done')

    console.log(chalk.green('\n✅ WordPress theme configured successfully!'))
}

run().catch(error => {
    if (error.isTtyError) {
        console.log(chalk.red('\n❌ The environment does not support Inquirer.'))
    } else {
        console.log(chalk.yellow('\n⚠️  Process canceled.'))
    }
    process.exit(0)
})
