#!/usr/bin/env node

import inquirer from 'inquirer'
import fs from 'fs-extra'
import chalk from 'chalk'
import path from 'path'

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

// Add content to a file
const addToFile = (filePath, contentToAdd) => {
    if (!fs.existsSync(filePath)) {
		console.log(chalk.red(`❌ The file ${filePath} does not exist.`))
        return
    }

    try {
		const filename = path.basename(filePath)
        fs.appendFileSync(filePath, `\n${contentToAdd}`)
        console.log(chalk.green(`✔ Update ${filename}`))
    } catch (error) {
        console.log(chalk.red(`❌ Error while editing file: ${error.message}`))
    }
}

// Add content to a file from another file
const addContentFromFile = (targetFile, sourceFile, replace = '') => {
    if (!fs.existsSync(targetFile)) {
        console.log(chalk.red(`❌ The file ${targetFile} does not exist.`))
        return
    }
    if (!fs.existsSync(sourceFile)) {
        console.log(chalk.red(`❌ The source file ${sourceFile} does not exist.`))
        return
    }

    try {
        const filename = path.basename(targetFile)
        let content = fs.readFileSync(sourceFile, 'utf-8')

		// Replace string in content
		if (replace) {
			content = content.replace(/\{\{name\}\}/g, replace)
		}

		const isEmpty = fs.readFileSync(targetFile, 'utf-8').trim().length === 0
		fs.appendFileSync(targetFile, isEmpty ? content : `\n${content}`)

        console.log(chalk.green(`✔ Updated ${filename} with content from ${sourceFile}`))
    } catch (error) {
        console.log(chalk.red(`❌ Error while editing file: ${error.message}`))
    }
};

// Filters
const filtersStringToSlug = (input, answers) => {
	return input
		.normalize('NFD')
		.replace(/[\u0300-\u036f]/g, '')
		.replace(/[^a-zA-Z0-9]/g, '-')
		.toLowerCase()
		.replace(/-+/g, '-')
		.replace(/^-+|-+$/g, '');
}

// Questions
const questions = [
    { name: 'filename', message: 'Filename:', validate: input => input ? true : 'File name cannot be empty.', filter: filtersStringToSlug }
]

// Create file
inquirer.prompt(questions).then(answers => {
    console.log(chalk.blue('\n🔄 Create files...\n'))

	// Create scss file
	const scssPath = path.join(process.cwd(), `assets/css/scss/_${answers.filename}.scss`)
    createFile(scssPath, '')
	addToFile('assets/css/scss/main.scss', `@import '${answers.filename}';`)

	// Create template file
	const templateFilename = `template-${answers.filename}.php`
	const templatePath = path.join(process.cwd(), templateFilename)
	createFile(templatePath, '')
	addContentFromFile(templateFilename, 'bin/structure/template.php', answers.filename)

	// Create view file
	const viewFilename = `views/pages/${answers.filename}.twig`
	const viewPath = path.join(process.cwd(), viewFilename)
	createFile(viewPath, '')
	addContentFromFile(viewFilename, 'bin/structure/view.twig', answers.filename)

	onsole.log(chalk.green('\n✅ Template files created successfully!'))
}).catch(error => {
	if (error.isTtyError) {
		console.log(chalk.red('\n❌ The environment does not support Inquirer.'))
	} else {
		console.log(chalk.yellow('\n⚠️  Process canceled.'))
	}
	process.exit(0)
})
